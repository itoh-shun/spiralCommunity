<?php

namespace spiralCommunity\App\Http\Controllers\Api;

use framework\Exception\NotFoundException;
use framework\Http\Controller;
use SiLibrary\SiDateTime;
use SiLibrary\SiValidator2;
use spiralCommunity\App\Services\OAuthService;

class UsersController extends Controller
{

    public function index(array $vars)
    {
        $page = (int)$this->request->get('page', 0);
        $limit = (int)$this->request->get('limit', 10);
        $users = \SpiralDB::title('users')->orderBy('id', 'asc')->changeLabelFields(['permission'])->value(
            ['id', 'display_name', 'userImage', 'email', 'permission',
                'office_location', 'birthplace',
                'gender',
                'joined',
                'memo'])
            ->page($page + 1)->paginate($limit);

        $user_re = [];

        foreach ($users as $user) {
            $user_re[] = [
                "name" => $user->display_name,
                "email" => $user->email,
                "userImage" => $user->userImage,
                "id" => $user->id,
                "permission" => $user->permission,
                "office_location" => $user->office_location,
                "birthplace" => $user->birthplace,
                "gender" => $user->gender,
                "joined" => $user->joined,
                "memo" => $user->memo,
            ];
        }

        echo json_encode([
            'items' => $user_re,
            'count' => $users->getTotal(),
            'page' => $page,
            'limit' => $limit,
        ], true);

    }

    public function create(array $vars)
    {
        //
    }

    public function store(array $vars)
    {
        $oauthService = new OAuthService(config('entrada'));
        $auth = $oauthService->authUser();

        if ($vars['userId'] !== $auth->id) {
            throw new NotFoundException();
        }

        $values = [
            'birthday' => $this->request->get('birthday', null),
            'birthplace' => $this->request->get('birthplace', null),
            'gender' => $this->request->get('gender', null),
            'joined' => $this->request->get('joined', null),
            'tags' => $this->request->get('tags', null),
        ];

        $rules = [
            'birthday' => ['date'],
            'birthplace' => ['max_bytes:128'],
            'gender' => ['in:male,female,other'],
            'joined' => ['date'],
            'tags' => ['json'],
        ];

        $validator = SiValidator2::make($values, $rules);
        $result = array_map(function ($re) {
            return $re->toArray();
        }, $validator->getResults());

        if (!$validator->isError()) {
            $gender = '';
            switch ($validator->getResults()['gender']->value()) {
                case 'male':
                    $gender = '1';
                    break;
                case 'female':
                    $gender = '2';
                    break;
                case 'other':
                    $gender = '3';
                    break;
                default:
                    break;
            };

            \SpiralDB::title('users')->upsert(
                'provider_id',
                [
                    'provider_id' => $auth->provider_id,
                    'birthday' => $values['birthday'],
                    'birthplace' => $values['birthplace'],
                    'gender' => $gender,
                    'joined' => $values['joined']
                ]
            );

            \SpiralDB::title('user_tags')->where('user_id', $auth->provider_id)->delete();

            \SpiralDB::title('user_tags')->insert(
                array_map(function ($id) use ($auth) {
                    return [
                        'user_id' => $auth->provider_id,
                        'tag_id' => $id
                    ];
                }, json_decode($values['tags']))
            );

        }

        echo json_encode(['result' => $result, 'error' => $validator->isError()], true);
    }

    private function getGenderLabel(int $gender = null)
    {
        if ($gender == '1') {
            return 'male';
        }
        if ($gender == '2') {
            return 'female';
        }
        if ($gender == '3') {
            return 'other';
        }

        return "";
    }

    public function show(array $vars)
    {
        $userId = $vars['userId'];
        $user = \SpiralDB::title('users')->orderBy('id', 'asc')->changeLabelFields(['permission'])->value(
            ['id', 'display_name', 'userImage', 'email', 'permission',
                'office_location', 'birthday', 'birthplace',
                'gender',
                'joined',
                'provider_id',
                'memo'])
            ->dataFormat('userImage', 'normal')
            ->findOrFail($userId);

        if ($user) {
            $tags = \SpiralDB::title('userTagsName')->value([
                'tag_name',
                'tag_id',
                'tag_category'
            ])->where('user_id', $user->provider_id)->get();
        }

        echo json_encode([
            "name" => $user->display_name,
            "email" => $user->email,
            "userImage" => $user->userImage,
            "id" => $user->id,
            "permission" => $user->permission,
            "office_location" => $user->office_location,
            "birthday" => $user->birthday ? (new SiDateTime($user->birthday))->format('Y-m-d') : '',
            "birthplace" => $user->birthplace,
            "gender" => $this->getGenderLabel($user->gender),
            "joined" => $user->joined ? (new SiDateTime($user->joined))->format('Y-m-d') : '',
            "tags" => array_map(function ($tag) {
                return [
                    'id' => $tag['tag_id'],
                    'name' => $tag['tag_name'],
                    'category' => $tag['tag_category'],
                ];
            }, $tags->toArray()),
            "memo" => $user->memo,
        ], true);
    }

    public function memoUpdate(array $vars)
    {
        $oauthService = new OAuthService(config('entrada'));
        $auth = $oauthService->authUser();

        if ($vars['userId'] !== $auth->id) {
            throw new NotFoundException();
        }

        $values = [
            'memo' => $this->request->get('memo', null),
        ];

        $rules = [
            'memo' => ['max_bytes:1024'],
        ];

        $validator = SiValidator2::make($values, $rules);
        $result = array_map(function ($re) {
            return $re->toArray();
        }, $validator->getResults());

        if (!$validator->isError()) {
            \SpiralDB::title('users')->upsert(
                'provider_id',
                [
                    'provider_id' => $auth->provider_id,
                    'memo' => $values['memo'],
                ]
            );
        }
        
        echo json_encode(['result' => $result, 'error' => $validator->isError()], true);
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
