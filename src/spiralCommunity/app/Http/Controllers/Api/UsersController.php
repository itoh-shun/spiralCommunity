<?php

namespace spiralCommunity\App\Http\Controllers\Api ;

use framework\Exception\NotFoundException;
use framework\Http\Request;
use framework\Http\Controller;
use framework\Http\View;
use framework\Support\ServiceProvider;
use SiLibrary\SiDateTime;
use SiLibrary\SiValidator2;
use spiralCommunity\App\Services\OAuthService;

class UsersController extends Controller
{

    public function index(array $vars)
    {
        $page = (int)$this->request->get('page' , 0);
        $limit = (int)$this->request->get('limit', 10);
        $users = \SpiralDB::title('users')->orderBy('id', 'asc')->changeLabelFields(['permission'])->value(
            ['id', 'display_name', 'userImage', 'email', 'permission',
                'office_location','birthplace',
                'gender',
                'joined',
                'memo'])
            ->page($page+1)->paginate($limit);

        $user_re = [];

        foreach($users as $user) {
            $user_re[] = [
                "name" => $user->display_name,
                "email"=> $user->email,
                "userImage"=> $user->userImage,
                "id"=> $user->id,
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

        if($vars['userId'] !== $auth->id) {
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
        $result = array_map(function($re){
            return $re->toArray();
        },$validator->getResults());

        if (! $validator->isError()) {
            $gender = '';
            switch ($validator->getResults()['gender']->value()) {
                case 'male':
                    $gender =  '1';
                    break;
                case 'female':
                    $gender =  '2';
                    break;
                case 'other':
                    $gender =  '3';
                    break;
                default:
                    break;
            };

            \SpiralDB::title('users')->upsert(
                'provider_id',
                [
                    'provider_id' => $auth->provider_id,
                    'birthday' => $values['birthday'],
                    'birthplace' =>$values['birthplace'],
                    'gender' => $gender,
                    'joined' => $values['joined']
                ]
            );
        }

        echo json_encode(['result' => $result , 'error' => $validator->isError() ], true);
    }

    private function getGenderLabel(int $gender = null){
        if($gender == '1'){
            return 'male';
        }
        if($gender == 'female'){
            return 'male';
        }
        if($gender == '3'){
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
                'memo'])
            ->dataFormat('userImage', 'normal')
            ->findOrFail($userId);

        echo json_encode([
            "name" => $user->display_name,
            "email"=> $user->email,
            "userImage"=> $user->userImage,
            "id"=> $user->id,
            "permission" => $user->permission,
            "office_location" => $user->office_location,
            "birthday" => (new SiDateTime( $user->birthday ))->format('Y-m-d'),
            "birthplace" => $user->birthplace,
            "gender" => $this->getGenderLabel($user->gender),
            "joined" => (new SiDateTime( $user->joined ))->format('Y-m-d'),
            "memo" => $user->memo,
        ], true);
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
