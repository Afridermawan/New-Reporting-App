<?php
 
namespace App\Controllers\api;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\GroupModel;
use App\Models\UserGroupModel;

class GroupController extends BaseController
{
	//Get All Group
	function index(Request $request, Response $response)
	{
		$group = new \App\Models\GroupModel($this->db);
		$get = $group->getAll();
		$countGroups = count($get);
		$query = $request->getQueryParams();
		if ($get) {
			$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
			$getGroup = $group->getAllGroup()->setPaginate($page, 5);

			if ($getGroup) {
				$data = $this->responseDetail(200, 'Data tersedia', [
						'query' 	=> 	$query,
						'result'	=>	$getGroup['data'],
						'meta'		=>	$getGroup['pagination'], 
					]);
			} else {
				$data = $this->responseDetail(404, 'Data tidak ditemukan', [
						'query'		=>	$query
					]);
			}
		} else {
			$data = $this->responseDetail(204, 'Tidak ada konten', [
					'query'		=>	$query,
					'result'	=>	$getGroup['data']
				]);
		}

		return $data;
	}

	//Find group by id
	function findGroup(Request $request, Response $response, $args)
	{
		$group = new \App\Models\GroupModel($this->db);
		$findGroup = $group->find('id', $args['id']);
		$query = $request->getQueryParams();

		if ($findGroup) {
			$data = $this->responseDetail(200, 'Data tersedia', [
					'query'		=>	$query,
					'result'	=>	$findGroup
				]);
		} else {
			$data = $this->responseDetail(404, 'Data tidak ditemukan', [
					'query'		=>	$query
				]);
		}

		return $data;
	}

	//Create group
	public function add(Request $request, Response $response)
	{
		$rules = [
			'required' => [
				['name'],
				['description'],
				['image'],
			]
		];

		$this->validator->labels([
			'name' 			=>	'Name',
			'description'	=>	'Description',
			'image'			=>	'Image',
		]);

		$this->validator->rules($rules);
		if ($this->validator->validate()) {
           
			$post = $request->getParams();

			$token = $request->getHeader('Authorization')[0];
			$userToken = new \App\Models\Users\UserToken($this->db);
			$post['creator'] = $userToken->getUserId($token);
			$query = $request->getQueryParams();
			$group = new \App\Models\GroupModel($this->db);
			$addGroup = $group->add($post);

			$findNewGroup = $group->find('id', $addGroup);

			$data = $this->responseDetail(201, 'Berhasil ditambahkan', [
					'query'		=>	$query,
					'result'	=>	$findNewGroup
				]);
		} else {
			$data = $this->responseDetail(400, $this->validator->errors());
		}

		return $data;
	}

	//Edit group
	public function update(Request $request, Response $response, $args)
	{
		$group = new \App\Models\GroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$findGroup = $group->find('id', $args['id']);
		$query = $request->getQueryParams();

		if ($findGroup) {
			$group->updateData($request->getParsedBody(), $args['id']);
			$afterUpdate = $group->find('id', $args['id']);

			$data = $this->responseDetail(200, 'Data berhasil di perbaharui', [
					'query'		=>	$query,
					'result'	=>	$afterUpdate
				]);
		} else {
			$data = $this->responseDetail(404, 'Data tidak ditemukan');
		}

		return $data;
	}

	//Delete group
	public function delete(Request $request, Response $response, $args)
	{
		$group = new \App\Models\GroupModel($this->db);
		$findGroup = $group->find('id', $args['id']);
		$query = $request->getQueryParams();

		if ($findGroup) {
			$group->hardDelete($args['id']);
			$data = $this->responseDetail(200, 'Berhasil menghapus data');
		} else {
			$data = $this->responseDetail(404, 'Data tidak ditemukan');
		}

		return $data;
	}

	//Set user as member of group
	public function setUserGroup(Request $request, Response $response)
	{
		$rules = [
			'required' => [
				['group_id'],
				['user_id']
			]
		];

		$this->validator->rules($rules);

		$this->validator->labels([
			'group_id' 	=>	'ID Group',
			'user_id'	=>	'ID User',
		]);

		if ($this->validator->validate()) {
			$userGroup = new \App\Models\UserGroupModel($this->db);
			$adduserGroup = $userGroup->add($request->getParsedBody());
			$query = $request->getQueryParams();

			$findNewGroup = $userGroup->find('id', $adduserGroup);

			$data = $this->responseDetail(201, 'User berhasil ditambahkan kedalam group', [
					'query'		=>	$query,
					'result'	=>	$findNewGroup
				]);
		} else {
			$data = $this->responseDetail(400, $this->validator->errors());
		}

		return $data;
	}

	//Get all user in group
	public function getAllUserGroup(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$users = new \App\Models\Users\UserModel($this->container->db);
		$userToken = new \App\Models\Users\UserToken($this->container->db);

		$finduserGroup = $userGroup->findUsers('group_id', $args['group']);
		$token = $request->getHeader('Authorization')[0];
		$findUser = $userToken->find('token', $token);
		$group = $userGroup->findUser('user_id', $findUser, 'group_id', $args['group']);
		$user = $users->find('id', $findUser);
		$query = $request->getQueryParams();

		if ($group) {
			if ($finduserGroup || $user['status'] == 1) {
				$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');

				$findAll = $userGroup->findAll($args['group'])->setPaginate($page, 10);

				$data = $this->responseDetail(200, 'Berhasil menampilkan data', [
					'query'		=>	$query,
					'result'	=>	$findAll
				]);
			} else {
				$data = $this->responseDetail(404, 'User tidak ditemukan didalam group', [
					'query'		=>	$query
				]);
			}
		} else {
			$data = $this->responseDetail(404, 'Kamu tidak terdaftar didalam group', [
					'query'		=>	$query
				]);
		}

		return $data;
	}

	//Get one user in group
	public function getUserGroup(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$users = new \App\Models\Users\UserModel($this->container->db);
		$userToken = new \App\Models\Users\UserToken($this->container->db);

		$finduserGroup = $userGroup->findUser('group_id', $args['group'], 'user_id', $args['id']);
		$token = $request->getHeader('Authorization')[0];
		$findUser = $userToken->find('token', $token);
		$group = $userGroup->findUser('user_id', $findUser['user_id'], 'group_id', $args['group']);
		$user = $users->find('id', $findUser['user_id']);
		$getUser = $userGroup->getUser($args['group'], $args['id']);
		$query = $request->getQueryParams();

		if ($group) {
			if ($finduserGroup) {
				$data = $this->responseDetail(200, 'Berhasil menampilkan data', [
					'query'		=>	$query,
					'result'	=>	$getUser
				]);
			} else {
				$data = $this->responseDetail(404, 'User tidak ditemukan didalam group', [
					'query'		=>	$query
				]);
			}
		} else {
			$data = $this->responseDetail(404, 'Kamu tidak terdaftar didalam group', [
					'query'		=>	$query
				]);
		}

		return $data;
	}

	//Delete user from group 
	public function deleteUser(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$users = new \App\Models\Users\UserModel($this->container->db);
		$userToken = new \App\Models\Users\UserToken($this->container->db);
		
		$token = $request->getHeader('Authorization')[0];
		$findUser = $userToken->find('token', $token);

		$finduserGroup = $userGroup->findUser('user_id', $args['id'], 'group_id', $args['group']);
		$group = $userGroup->findUser('user_id', $findUser['user_id'], 'group_id', $args['group']);

		$finduser = $userGroup->find('user_id', $args['id']);
		$user = $users->find('id', $findUser['user_id']);
		$query = $request->getQueryParams();

		if ($group) {
			if ($finduser) {
				$userGroup->hardDelete($finduserGroup['id']);

				$data = $this->responseDetail(200, 'User berhasil dihapus dari group', [
						'query'		=>	$query,
						'result'	=>	$userGroup
					]);
			} else {
				$data = $this->responseDetail(400, 'Anda tidak memiliki hak akses');
			}

		} else {
			$data = $this->responseDetail(404, 'Data tidak ditemukan', [
					'query'		=>	$query
				]);
		}

		return $data;
	}

	//Set user in group as member
	public function setAsMember(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$finduserGroup = $userGroup->findUser('user_id', $args['id'], 'group_id', $args['group']);
		$query = $request->getQueryParams();

		if ($finduserGroup) {
			$userGroup->setUser($finduserGroup['id']);

			$data = $this->responseDetail(200, 'User berhasil dijadikan member',  [
					'query'		=>	$query,
					'result'	=>	$userGroup
				]);
		} else {
			$data = $this->responseDetail(404, 'User tidak ditemukan didalam group', [
					'query'		=>	$query
				]);
		}

		return $data;
	}

	//Set user in group as PIC
	public function setAsPic(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$finduserGroup = $userGroup->findUser('user_id', $args['id'], 'group_id', $args['group']);
		$query = $request->getQueryParams();

		if ($finduserGroup) {
			$userGroup->setPic($finduserGroup['id']);

			$data = $this->responseDetail(200, 'User berhasil dijadikan PIC', [
					'query'		=>	$query,
					'result'	=>	$userGroup
				]);
		} else {
			$data = $this->responseDetail(404, 'User Tidak ditemukan di dalam group', [
					'query'		=>	$query,
				]);
		}

		return $data;
	}

	//Set user in group as guardian
	public function setAsGuardian(Request $request, Response $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$finduserGroup = $userGroup->findUser('user_id', $args['id'], 'group_id', $args['group']);
		$query = $request->getQueryParams();

		if ($finduserGroup) {
			$userGroup->setGuardian($finduserGroup['id']);

			$data = $this->responseDetail(200, 'User berhasil dijadikan Guardian', [
					'query'		=>	$query,
					'result'	=>	$finduserGroup
				]);
		} else {
			$data = $this->responseDetail(404, 'User tidak ditemukan di dalam group', [
					'query'		=>	$query
				]);
		}

		return $data;
	}

	public function getGroup(Request $request, Response $response)
	{
		$group = new GroupModel($this->db);
		$userGroup = new \App\Models\UserGroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);
		$query = $request->getQueryParams();
		
		if ($group) {
			$getGroup = $userGroup->findAllGroup($userId);
			$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
			$get = $group->getAllGroup()->setPaginate($page, 5);

			$data = $this->responseDetail(200, 'Berhasil menampilkan data', [
					'query'		=>	$query,
					'result'	=>	$get['data'],
					'meta'		=>	$get['pagination']
				]);
		}else {
			$data = $this->responseDetail(404, 'Data tidak ditemukan');
		}

		return $data;
	}

	//Find group by id
	public function delGroup(Request $request, Response $response, $args)
	{
		$group = new GroupModel($this->db);
		$userGroup = new UserGroupModel($this->db);
		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);

		$findGroup = $group->find('id', $args['id']);
		$finduserGroup = $userGroup->findUsers('group_id', $args['id']);
		$pic = $userGroup->finds2('group_id', $args['id'], 'user_id', $userId);
		$query = $request->getQueryParams();

		if ($userId == 1 || $pic[0]['status'] == 1) {
			$delete = $group->hardDelete($args['id']);

			$data = $this->responseDetail(200, 'Data berhasil di hapus');
		} else {
			$data = $this->responseDetail(400, 'Ada masalah saat menghapus data', [
					'query'		=>	$query
				]);
		}

		return $data;
	}
	
	//Set user as member of group
	public function joinGroup(Request $request, Response $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);
		$query = $request->getQueryParams();

		$findUser = $userGroup->finds('user_id', $userId, 'group_id', $args['id']);

		$data = [	
			'group_id' 	=> 	$args['id'],
			'user_id'	=>	$userId,
		];

		if ($findUser[0]) {
			$data = $this->responseDetail(400, 'Anda sudah tergabung ke grup');
		} else {
			$addMember = $userGroup->createData($data);

			$data = $this->responseDetail(200, 'Anda berhasil bergabung dengan grup',  [
					'query'		=>	$query,
					'result'	=>	$data
				]);
		}

		return $data;
	}

	//leave group
	public function leaveGroup(Request $request, Response $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$posts = new \App\Models\PostModel($this->db);
		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);
		$query = $request->getQueryParams();

		$group = $userGroup->finds('user_id', $userId, 'group_id', $args['id']);
		$findPost = $posts->finds('creator', $userId, 'group_id', $args['id']);

		if ($group[0]) {

			if ($findPost) {
				foreach ($findPost as $key => $value) {
					$post_del = $posts->hardDelete($value['id']);
				}
			}

			$leaveGroup = $userGroup->hardDelete($group[0]['id']);

			$data = $this->responseDetail(200, 'Anda telah meninggalkan grup');
		} else {
			$data = $this->responseDetail(400, 'Anda tidak tergabung di grup ini');

		}

		return $data;
	}

	//search group
	public function searchGroup(Request $request, Response $response)
    {
        $group = new GroupModel($this->db);
        $token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);
		$query = $request->getQueryParams();

        $search = $request->getParams()['search'];

        // $data['search'] = $request->getQueryParam('search');
		$data['groups'] =  $group->search($search);
        $data['count'] = count($data['groups']);
        // var_dump($data);die();
        // $_SESSION['search'] = $data;
        if ($data['count']) {
        	$data = $this->responseDetail(200, 'Berhasil menampilkan data search', [
        			'query'		=>	$query,
        			'result'	=>	$data
        		]);
        }else {
        	$data = $this->responseDetail(404, 'Data tidak ditemukan');
        }

        return $data;
    }

    public function postImage($request, $response, $args)
    {
        $group = new GroupModel($this->db);

        $findGroup = $group->find('id', $args['id']);
        if (!$findGroup) {
            return $this->responseDetail(404, 'Data tidak ditemukan');
        }

        if (!empty($request->getUploadedFiles()['image'])) {
            $storage = new \Upload\Storage\FileSystem('assets/images');
            $image = new \Upload\File('image', $storage);

            $image->setName(uniqid('img-'.date('Ymd').'-'));
            $image->addValidations(array(
                new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
                'image/jpg', 'image/jpeg')),
                new \Upload\Validation\Size('5M')
            ));

            $image->upload();
            $data['image'] = $image->getNameWithExtension();

            $group->updateData($data, $args['id']);
            $newGroup = $group->find('id', $args['id']);

            return  $this->responseDetail(200, 'Foto berhasil diunggah', [
                'result' => $newGroup
            ]);

        } else {
            return $this->responseDetail(400, 'File foto belum dipilih');
        }
    }

    public function inActive($request, $response)
    {
    	$group = new GroupModel($this->db);

    	$getGroup = $group->getInActive();
    	$countGroups = count($getGroup);
    	$query = $request->getQueryParams();
    	$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$get = $group->getAllGroupNonActive()->setPaginate($page, 5);

    	if ($countGroups == 0) {
    		return $this->responseDetail(404, 'Data tidak ditemukan');
    	} else {
    		return $this->responseDetail(200, 'Berhasil menampilkan data', [
    			'query'			=>	$query,	
    			'result' 		=> 	$get['data'],
    			'pagination'	=>	$get['pagination'] 
    		]);
    	}
    }

    public function getPicGroup($request, $response)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);

		$getGroup = $userGroup->picGroup($userId);
		$query = $request->getQueryParams();

		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$get = $getGroup->setPaginate($page, 5);

		if ($getGroup == 0) {
			return $this->responseDetail(404, 'Data tidak ditemukan');
		} else {
			return $this->responseDetail(200, 'Berhasil menampilkan data', [
				'query'			=>	$query,
				'result'		=>	$get['data'],
				'pagination'	=>	$get['pagination']
			]);
		}
	}

    public function setInActive(Request $request, Response $response, $args)
    {
		$group = new GroupModel($this->db);
		$findGroup = $group->find('id', $args['id']); 

    	if (!$findGroup) {
    		return $this->responseDetail(400, 'Ada masalah saat menghapus data');
    	} else {
    		$group->softDelete($args['id']);
    		return $this->responseDetail(200, 'Berhasil menghapus data');
    	}
    }

    //Set restore group
	public function restore(Request $request, Response $response, $args)
	{
		$group = new GroupModel($this->db);
		$findGroup = $group->find('id', $args['id']);
		$query = $request->getQueryParams();

		if (!$findGroup) {
			return $this->responseDetail(400, 'Ada masalah saat mengembalikan data');
		} else {
			$group->restore($args['id']);
			$get = $group->find('id', $args['id']);

			return $this->responseDetail(200, 'Berhasil mengembalikan data', [
				'query'		=>	$query,
				'result'	=>	$get
			]);
		}
	}

	public function getPic($request, $response, $args)
	{
		$userGroup = new \App\Models\UserGroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);

		$getGroup = $userGroup->findAllUser($userId);
		$query = $request->getQueryParams();

		if ($getGroup) {
			return $this->responseDetail(200, 'Berhasil menampilkan data', [
				'query'		=>	$query,
				'result'	=>	$getGroup
			]);
		} else {
			return $this->responseDetail(400, 'Ada kesalahan saat menampilkan data');
		}
	}

	//Get all user in group
	public function getMemberGroup($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$groups = new GroupModel($this->db);
		$users = new \App\Models\Users\UserModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);

		$pic = $userGroup->finds('group_id', $args['id'], 'user_id', $userId);
		$member = $userGroup->getMember($args['id']);
		$group = $groups->find('id', $args['id']);
		$query = $request->getQueryParams();

		if ($userId == 1 || $pic[0]['status'] == 1) {
			return $this->responseDetail(200, 'Berhasil menampilkan data', [
				'query' 			=> $query,
				'result'			=> $member,
			]);
		} else {
			return $this->responseDetail(400, 'Anda tidak memiliki akses ke user ini!');
		}
	}

	//Get all user in group
	public function getNotMember($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);

		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$users = $userGroup->notMember($args['id'])->setPaginate($page, 5);
		$pic = $userGroup->findUser('group_id', $args['id'], 'user_id', $userId);
		$query = $request->getQueryParams();

		if ($userId == 1 || $pic['status'] == 1) {
			return $this->responseDetail(200, 'Berhasil menampilkan data', [
				'query'			=>	$query,
				'result' 		=> 	$users,
				// 'pagination'	=> 	$pic['pagination']
			]);
		} else {
			return $this->responseDetail(400, 'Anda tidak memiliki akses ke user ini!');
		}
	}

	//Post create group
	public function createByUser($request, $response)
	{
		$rules = ['required' => [['name'], ['description']] ];
		$this->validator->rules($rules);

		$this->validator->labels([
			'name' 			=>	'Name',
			'description'	=>	'Description',
			'image'			=>	'Image',
		]);

		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$userId = $userToken->getUserId($token);

		if ($this->validator->validate()) {
			$dataGroup = [
				'name' 			=>	$request->getParams()['name'],
				'description'	=>	$request->getParams()['description'],
				'image'			=>	$request->getParams()['image'],
				'creator'       =>  $userId
			];

			$group = new GroupModel($this->db);
			$userGroup = new \App\Models\UserGroupModel($this->db);

			$addGroup = $group->add($dataGroup);

			$data = [
				'group_id' 	=> 	$addGroup,
				'user_id'	=>	$userId,
				'status'	=>	1,
			];

			$addUserGroup = $userGroup->add($data);
			$newUserGroup = $userGroup->find('id', $addUserGroup);

			$query = $request->getQueryParams();
			return $this->responseDetail(201, 'Berhasil Membuat group', [
				'query'		=>	$query,
				'result'	=>	$newUserGroup
			]);

		} else {
			return $this->responseDetail(401, 'Ada kesalahan saat membuat group');
		}
	}

	//Set user as member of group
	public function setMemberGroup($request, $response, $args)
	{
		$userGroups = new UserGroupModel($this->db);

		$token = $request->getHeader('Authorization')[0];
		$userToken = new \App\Models\Users\UserToken($this->db);
		$user = $userToken->getUserId($token);

		$userId = $request->getParams()['user_id'];
		$groupId = $request->getParams()['group_id'];
		$pic = $userGroups->finds('group_id', $groupId, 'user_id', $user);
		$userGroup = $userGroups->finds('group_id', $groupId, 'user_id', $userId);

		if (!$userGroup) {
			if ($user == 1 || $pic[0]['status'] == 1) {
				$data = [
					'group_id' 	=> 	$groupId,
					'user_id'	=>	$userId,
					'status'	=>	0
				];

				$addMember = $userGroups->createData($data);
				$findMember = $userGroups->finds('user_id', $userId, 'group_id', $groupId);

				return $this->responseDetail(201, 'Anda berhasil menambahkan user kedalam group !', [
					'result'	=>	$findMember
				]);
			} else {
				return $this->responseDetail(400, 'Anda tidak memiliki akses !');
			}

		}else {
			return $this->responseDetail(400, 'Member sudah tergabung!');
		}

		// if ($user == 2 && $pic[0]['status'] == 1) {
		// 	return $response->withRedirect($this->router
		// 	->pathFor('pic.member.group.get', ['id' => $groupId]));

		// } else {
		// 	return $response->withRedirect($this->router
		// 	->pathFor('user.group.get', ['id' => $groupId]));
		// }
	}
}