<?php

namespace spiralCommunity\App\Http\Controllers\Api;

use framework\Http\Controller;

class TagsController extends Controller
{

    public function index(array $vars)
    {
        $tags = \SpiralDB::title('tags')->orderBy('id', 'asc')->value(
            ['tag_id', 'tag_name', 'tag_category'])
            ->get();

        $tags_re = [];

        foreach ($tags as $tag) {
            $tags_re[] = [
                'id' => $tag->tag_id,
                'name' => $tag->tag_name,
                'category' => $tag->tag_category,
            ];
        }

        echo json_encode($tags_re, true);

    }

    public function create(array $vars)
    {
        //
    }

    public function store(array $vars)
    {

    }

    public function show(array $vars)
    {
    }

    public function edit(array $vars)
    {
        //
    }

    public function update(array $vars)
    {
        //
    }

    public function destroy(array $vars)
    {
        //
    }
}
