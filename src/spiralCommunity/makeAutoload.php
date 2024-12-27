
<?php

function topological_sort($graph)
{
    $result = [];
    $visited = [];
    foreach ($graph as $node => $edges) {
        topological_sort_visit($node, $graph, $visited, $result);
    }
    return array_reverse($result);
}

function topological_sort_visit($node, $graph, &$visited, &$result): void
{
    if (!isset($visited[$node])) {
        $visited[$node] = true;
        foreach ($graph[$node] as $edge) {
            topological_sort_visit($edge, $graph, $visited, $result);
        }
        $result[] = $node;
    }
}

function extractClassesFromStatement($pattern, $fileContent) {
    preg_match_all($pattern, $fileContent, $matches);
    $allImports = [];
    foreach ($matches[1] as $match) {
        $imports = array_map('trim', explode(',', $match));
        $allImports = array_merge($allImports, $imports);
    }
    return $allImports;
}


$classToFileMap = require_once $argv[1].'/vendor/composer/autoload_classmap.php';
$classToFileMap = array_filter($classToFileMap, function ($path) {
    return strpos($path, getcwd()) !== false;
});
$classToFileMapFiles = array_values($classToFileMap);

$directory = new RecursiveDirectoryIterator('./', RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
$iterator = new RecursiveIteratorIterator($directory);
$regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

// ディレクトリから取得されるファイルのリスト
$files = [];
foreach ($regex as $file) {
    $files[] = $file[0];
}

usort($files, function ($a, $b) {
    return strcmp($a, $b);
});

$dependencies = [];

foreach ($files as $file) {
    if (strpos($file, './.') === 0) {
        continue;
    }
    if (strpos($file, './resources') === 0) {
        continue;
    }
    if (strpos($file, './routes') === 0) {
        continue;
    }
    if($file === './makeAutoload.php'){        
        continue;
    }
    if($file === './autoload_static.php'){
        continue;
    }
    if($file === './makeAutoloadCustom.php'){
        continue;
    }
    $fileContent = file_get_contents($file);
    $fileContent = preg_replace('!/\*.*?\*/!s', "", $fileContent); // マルチラインのコメントを削除
    $fileContent = preg_replace('!//.*?$!m', "", $fileContent);    // シングルラインのコメントを削除
    
    // namespace の解析
    if(preg_match('/\bnamespace\s+([^{\s]+)\s*{?/', $fileContent, $namespaceMatch)) {
        $namespace = $namespaceMatch[1];
    } else {
        $namespace = "";
    }
    // use ステートメントの解析
    $useMatches = extractClassesFromStatement('/\buse\s+([^(;]+);/', $fileContent);

    // extends ステートメントの解析
    $extendsMatches = extractClassesFromStatement('/\bextends\s+([^\s;]+)/', $fileContent);

    // trait の use ステートメントの解析
    //$traitMatches = extractClassesFromStatement('/\buse\s+([^(;]+);/', $fileContent);

    // プロパティの型の解析
    $propertyTypeMatches = extractClassesFromStatement('/(?:private|protected|public)\s+(?:static\s+)?\??([^\s$]+)\s+\$[^\s;]+/', $fileContent);

    $methodArgTypeMatches = extractClassesFromStatement('/\s*\??([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)(?:\[\])?\s*\$/', $fileContent);

    $implementsMatches = extractClassesFromStatement('/\bimplements\s+([^{\s]+)(?:,\s*[^{\s]+)*/', $fileContent);

    $allMatches = array_merge($useMatches, $extendsMatches, $propertyTypeMatches, $methodArgTypeMatches, $implementsMatches);
    
    if (empty($namespace) && empty($allMatches)) {
        $dependencies[$file] = [];
    } else {
        $dependencies[$file] = $allMatches;  // 元の依存関係を保持
        foreach ($allMatches as $class) {
            // 名前空間が指定されていない場合、ファイルの名前空間を付与する
            if ($namespace && strpos($class, "\\") !== 0) {
                // セミコロンを取り除いた名前空間を使用して結合
                $combinedNamespace = rtrim($namespace, ';') . "\\" . $class;
                $dependencies[$file][] = $combinedNamespace;  // 名前空間を付与した依存関係を追加
            }
        }
    }
}

function resolveDependencies($file, $dependencies, &$resolved, &$seen): void
{
    $seen[$file] = true;
    foreach ($dependencies[$file] as $dependency) {
        if (!isset($resolved[$dependency]) && isset($dependencies[$dependency])) {
            resolveDependencies($dependency, $dependencies, $resolved, $seen);
        }
    }
    $resolved[$file] = true;
}

$resolved = [];
$seen = [];

foreach (array_keys($dependencies) as $file) {
    if (!isset($seen[$file])) {
        resolveDependencies($file, $dependencies, $resolved, $seen);
    }
}

$newDependencies = [];
foreach ($dependencies as $relativeFile  => $classes) {
    $file = ltrim($relativeFile, './');
    $file = __DIR__ . '/' . $file; // __DIR__ は現在のスクリプトのディレクトリ
    $file = str_replace('//', '/', $file); // 重複するスラッシュを排除
    //$class = array_search($file, $classToFileMap);
    //if ($class !== false) {
    $newDependencies[$file] = [];
    foreach ($classes as $depClass) {
        if (array_key_exists($depClass, $classToFileMap)) {
            $newDependencies[$file][] = $classToFileMap[$depClass];
        }
    }
    //}
}

$baseDir = getcwd();
$currentDirName = basename(getcwd());  // 現在のディレクトリ名を取得
$autoloadFileContent = "<?php \r\n";
foreach(array_reverse(topological_sort($newDependencies)) as $file) {
    $libraryPathStartPos = strpos($baseDir, "Library");
    if ($libraryPathStartPos !== false) {
        $libraryRelativePath = substr($baseDir, $libraryPathStartPos);
    } else {
        $libraryRelativePath = $currentDirName;
    }
    $relativePath = $libraryRelativePath . '/' . str_replace($baseDir, '', $file);  // ディレクトリ名を付与
    $relativePath = str_replace('//', '/', $relativePath);
    $autoloadFileContent .= "require_once '{$relativePath}';\r\n";
}

file_put_contents('autoload_static.php', $autoloadFileContent);
