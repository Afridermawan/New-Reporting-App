<?php 

namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\GroupModel;
use App\Models\UserGroupModel;

class GroupController extends BaseController
{
	//Get active Group
	function index($request, $response)
	{
		$group = new GroupModel($this->db);
		$article = new \App\Models\ArticleModel($this->db);
		$user = new \App\Models\Users\UserModel($this->db);
		$item = new \App\Models\Item($this->db);

		$getGroup = $group->getAll();

		$countGroup = count($getGroup);
		$countArticle = count($article->getAll());
		$countUser = count($user->getAll());
		$countItem = count($item->getAll());

		$data = $this->view->render($response, 'admin/group/index.twig', [
			'groups' => $getGroup,
			'counts'=> [
				'group' => $countGroup,
				'article' => $countArticle,
				'user' => $countUser,
				'item' => $countItem,
			]
		]);

		return $data;
	}
 
	// //Get inactive group
	function inActive($request, $response)
	{
		$group = new GroupModel($this->db);
		$article = new \App\Models\ArticleModel($this->db);
		$user = new \App\Models\Users\UserModel($this->db);
		$item = new \App\Models\Item($this->db);

		$getGroup = $group->getInActive();

		$countGroup = count($getGroup);
		$countArticle = count($article->getAll());
		$countUser = count($user->getAll());
		$countItem = count($item->getAll());

		$data = $this->view->render($response, 'admin/group/inactive.twig', [
			'groups' => $getGroup,
			'counts'=> [
				'group' => $countGroup,
				'article' => $countArticle,
				'user' => $countUser,
				'item' => $countItem,
			]
		]);

		return $data;
	}

	//Find group by id
	function findGroup($request, $response, $args)
	{
		$group = new GroupModel($this->db);
		$userGroup = new UserGroupModel($this->db);

		$findGroup = $group->find('id', $args['id']);
		$finduserGroup = $userGroup->findUsers('group_id', $args['id']);
		$countUser = count($finduserGroup);
		$pic = $userGroup->findUser('group_id', $args['id'], 'user_id', $_SESSION['login']['id']);
// var_dump($pic);die();
		if ($_SESSION['login']['status'] == 1 || $pic['status'] == 1) {
			return $this->view->render($response, 'admin/group/detail.twig', [
				'group' => $findGroup,
				'counts'=> [
					'user' => $countUser,
				]
			]);
		} else {
			$this->flash->addMessage('error', 'Anda tidak memiliki akses di grup ini!');
			return $response->withRedirect($this->router
					->pathFor('home'));
		}
	}

	//Get create group
	public function getAdd($request, $response)
	{
		return $this->view->render($response, 'admin/group/add.twig');
	}

	//Post create group
	public function add($request, $response)
	{
        $storage = new \Upload\Storage\FileSystem('assets/images');
        $image = new \Upload\File('image',$storage);
        $image->setName(uniqid());
        $image->addValidations(array(
            new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
            'image/jpg', 'image/jpeg')),
            new \Upload\Validation\Size('5M')
        ));
        $dataImg = array(
          'name'       => $image->getNameWithExtension(),
          'extension'  => $image->getExtension(),
          'mime'       => $image->getMimetype(),
          'size'       => $image->getSize(),
          'md5'        => $image->getMd5(),
          'dimensions' => $image->getDimensions()
        );
		$rules = ['required' => [['name'], ['description']] ];
		$this->validator->rules($rules);
		$this->validator->labels([
			'name' 			=>	'Name',
			'description'	=>	'Description',
			'image'			=>	'Image',
		]);
		$userId  = $_SESSION['login']['id'];
		if ($this->validator->validate()) {
			if (!empty($_FILES['image']['name'])) {
                $image->upload();
                $imageName = $dataImg['name'];
            } else {
                $imageName = '';
            }
			$data = [
				'name' 			=>	$request->getParams()['name'],
				'description'	=>	$request->getParams()['description'],
				'image'			=>	$imageName,
				'creator'		=>	$userId,
			];
			$group = new GroupModel($this->db);
			$addGroup = $group->add($data);
			$this->flash->addMessage('succes', 'Grup berhasil dibuat');
			return $response->withRedirect($this->router
							->pathFor('create.group.get'));
		} else {
			$_SESSION['old'] = $request->getParams();
			$_SESSION['errors'] = $this->validator->errors();
			return $response->withRedirect($this->router->pathFor('create.group.get'));
		}
	}

	//Get edit group
	public function getUpdate($request, $response, $args)
	{
		$group = new GroupModel($this->db);
        $data['group'] = $group->find('id', $args['id']);
		return $this->view->render($response, 'admin/group/edit.twig', $data);
	}

	//Post Edit group
	public function update($request, $response, $args)
	{
		$group = new GroupModel($this->db);
		$rules = ['required' => [['name'], ['description']] ];

		$this->validator->rules($rules);
		$this->validator->labels([
						'name' 			=>	'Name',
						'description'	=>	'Description',
						'image'			=>	'Image',
						]);

		if ($this->validator->validate()) {
			if (!empty($_FILES['image']['name'])) {

				$storage = new \Upload\Storage\FileSystem('assets/images');
				$file = new \Upload\File('image', $storage);
				$file->setName(uniqid());
				$file->addValidations(array(
				new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
				'image/jpg', 'image/jpeg')),
				new \Upload\Validation\Size('5M')
				));

				$dataImg = array(
				'name'       => $file->getNameWithExtension(),
				'extension'  => $file->getExtension(),
				'mime'       => $file->getMimetype(),
				'size'       => $file->getSize(),
				'md5'        => $file->getMd5(),
				'dimensions' => $file->getDimensions()
				);

				$data = [
				'name' 			=>	$request->getParams()['name'],
				'description'	=>	$request->getParams()['description'],
				'image'			=>	$dataImg['name'],
				];

				$file->upload();
				$group->updateData($data, $args['id']);
			} else {
				$group->updateData($request->getParams(), $args['id']);
			}

			if ($_SESSION['login']['status'] == 1) {
				return $response->withRedirect($this->router->pathFor('group.list'));
			} else {
				return $response->withRedirect($this->router
		        ->pathFor('enter.group', ['id' => $args['id']]));

			}

		} else {
			$_SESSION['old'] = $request->getParams();
			$_SESSION['errors'] = $this->validator->errors();
			return $response->withRedirect($this->router
			->pathFor('edit.group.get', ['id' => $args['id']]));
		}
	}

	//Set inactive/soft delete group
	public function setInactive($request, $response)
	{
		foreach ($request->getParam('group') as $key => $value) {
			$group = new GroupModel($this->db);
			$group_del = $group->softDelete($value);
		}

		return $response->withRedirect($this->router->pathFor('group.list'));
	}

	//Set active/restore group
	public function setActive($request, $response)
	{
		if (!empty($request->getParams()['restore'])) {
			foreach ($request->getParam('group') as $key => $value) {
				$group = new GroupModel($this->db);
				$group_del = $group->restore($value);
			}
		} elseif (!empty($request->getParams()['delete'])) {
			foreach ($request->getParam('group') as $key => $value) {
				$group = new GroupModel($this->db);
				$group_del = $group->hardDelete($value);
			}
		}

		return $response->withRedirect($this->router->pathFor('group.inactive'));
	}

	//Set user as member or PIC of group
	public function setUserGroup($request, $response)
	{
		$userGroup = new UserGroupModel($this->db);
		$groupId = $request->getParams()['id'];
		$pic = $userGroup->findUser('group_id', $groupId, 'user_id', $_SESSION['login']['id']);
// var_dump($request->getParam('user'));die();
		if ($_SESSION['login']['status'] == 1 || $pic['status'] == 1) {
			if (!empty($request->getParams()['pic'])) {
				foreach ($request->getParam('user') as $key => $value) {
					$finduserGroup = $userGroup->findUser('user_id', $value, 'group_id', $groupId);
					$userGroup->setPic($finduserGroup['id']);
				}
			} elseif (!empty($request->getParams()['member'])) {
				foreach ($request->getParam('user') as $key => $value) {
					$finduserGroup = $userGroup->findUser('user_id', $value, 'group_id', $groupId);
					$userGroup->setUser($finduserGroup['id']);
				}
			} elseif (!empty($request->getParams()['delete'])) {
				foreach ($request->getParam('user') as $key => $value) {
					$finduserGroup = $userGroup->findUser('user_id', $value, 'group_id', $groupId);
					$userGroup->hardDelete($finduserGroup['id']);
				}
			}

			if ($_SESSION['login']['status'] == 2 && $pic['status'] == 1) {
				return $response->withRedirect($this->router->pathFor('pic.member.group.get', ['id' => $groupId]));
			}

			return $response->withRedirect($this->router->pathFor('user.group.get', ['id' => $groupId]));

		} else {
			$this->flash->addMessage('error', 'Anda tidak memiliki akses ke user ini!');
			return $response->withRedirect($this->router
			->pathFor('home'));
		}
	}

	//Get all user in group
	public function getMemberGroup($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$groups = new GroupModel($this->db);
		$users = new \App\Models\Users\UserModel($this->db);

		$user= $_SESSION['login'];
		$pic = $userGroup->finds('group_id', $args['id'], 'user_id', $user['id']);
		$member = $userGroup->getMember($args['id']);
		$group = $groups->find('id', $args{'id'});

		if ($user['status'] == 1 || $pic[0]['status'] == 1) {
			return $this->view->render($response, 'pic/groupmember.twig', [
				'members' 	=> $member,
				'group_id'	=> $group['id'],
				'group'		=> $group['name'],
			]);
		} else {
			$this->flash->addMessage('error', 'Anda tidak memiliki akses ke user ini!');
			return $response->withRedirect($this->router
			->pathFor('home'));
		}
	}

	//Get all user in group
	public function getNotMember($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);

		$page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
		$users = $userGroup->notMember($args['id'])->setPaginate($page, 10);
		$pic = $userGroup->findUser('group_id', $args['id'], 'user_id', $_SESSION['login']['id']);

		if ($_SESSION['login']['status'] == 1 || $pic['status'] == 1) {
			return $this->view->render($response, 'admin/group/not-member.twig', [
				'users' => $users['data'],
				'group_id'	=> $args['id']
			]);
		} else {
			$this->flash->addMessage('error', 'Anda tidak memiliki akses ke user ini!');
			return $response->withRedirect($this->router
			->pathFor('home'));
		}
	}

	//Set user as member of group
	public function setMemberGroup($request, $response, $args)
	{
		$userGroups = new UserGroupModel($this->db);

		$groupId = $request->getParams()['group_id'];
		$userId = $request->getParams()['user_id'];
		$pic = $userGroups->finds('group_id', $groupId, 'user_id', $_SESSION['login']['id']);
		$userGroup = $userGroups->finds('group_id', $groupId, 'user_id', $userId);
		// var_dump($request->getParams());die();
		if ($userGroup) {
			$this->flash->addMessage('error', 'Member sudah tergabung!');

		}else {
			if ($_SESSION['login']['status'] == 1 || $pic[0]['status'] == 1) {
				$data = [
					'group_id' 	=> 	$groupId,
					'user_id'	=>	$userId,
				];

				$addMember = $userGroups->createData($data);
			} else {
				$this->flash->addMessage('error', 'Anda tidak memiliki akses !');
				return $response->withRedirect($this->router
				->pathFor('home'));
			}
		}

		if ($_SESSION['login']['status'] == 2 && $pic[0]['status'] == 1) {
			return $response->withRedirect($this->router
			->pathFor('pic.member.group.get', ['id' => $groupId]));

		} else {

			return $response->withRedirect($this->router
			->pathFor('user.group.get', ['id' => $groupId]));
		}
	}

	function getGroup($request, $response)
	{
		$group = new GroupModel($this->db);
		$article = new \App\Models\ArticleModel($this->db);
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$item = new \App\Models\Item($this->db);

		$userId  = $_SESSION['login']['id'];
		$getGroup = $userGroup->findAllGroup($userId);

		return $this->view->render($response, 'users/group/group.twig', [
			'groups' => $getGroup,

		]);

	}

	function getPic($request, $response)
	{
		$group = new GroupModel($this->db);
		$article = new \App\Models\ArticleModel($this->db);
		$userGroup = new \App\Models\UserGroupModel($this->db);
		$item = new \App\Models\Item($this->db);

		$userId  = $_SESSION['login']['id'];
		$getGroup = $userGroup->findAllUser(1);
	// var_dump($getGroup);die();
		return $this->view->render($response, 'users/pic-group.twig', [
			'groups' => $getGroup,
		]);
	}

	function getPicGroup($request, $response)
	{
		$group = new GroupModel($this->db);
		$item = new \App\Models\Item($this->db);
		$article = new \App\Models\ArticleModel($this->db);
		$userGroup = new \App\Models\UserGroupModel($this->db);

		$userId  = $_SESSION['login']['id'];
		$getGroup = $userGroup->picGroup($userId);

		return $this->view->render($response, 'pic/pic-group.twig', [
			'groups' => $getGroup,
		]);
	}

	//Post create group
	public function createByUser($request, $response)
	{
		$storage = new \Upload\Storage\FileSystem('assets/images');
		$image = new \Upload\File('image',$storage);
		$image->setName(uniqid());
		$image->addValidations(array(
			new \Upload\Validation\Mimetype(array('image/png', 'image/gif',
			'image/jpg', 'image/jpeg')),
			new \Upload\Validation\Size('5M')
		));

		$dataImg = array(
		  'name'       => $image->getNameWithExtension(),
		  'extension'  => $image->getExtension(),
		  'mime'       => $image->getMimetype(),
		  'size'       => $image->getSize(),
		  'md5'        => $image->getMd5(),
		  'dimensions' => $image->getDimensions()
		);
		$rules = ['required' => [['name'], ['description']] ];
		$this->validator->rules($rules);

		$this->validator->labels([
			'name' 			=>	'Name',
			'description'	=>	'Description',
			'image'			=>	'Image',
		]);

		$userId  = $_SESSION['login']['id'];

		if ($this->validator->validate()) {
			if (!empty($_FILES['image']['name'])) {
                $image->upload();
                $imageName = $dataImg['name'];
            } else {
                $imageName = '';
            }

			$dataGroup = [
				'name' 			=>	$request->getParams()['name'],
				'description'	=>	$request->getParams()['description'],
				'image'			=>	$imageName,
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
			$userGroup->createData($data);

			$this->flash->addMessage('succes', 'Grup berhasil dibuat');

		} else {
			$_SESSION['old'] = $request->getParams();
			$_SESSION['errors'] = $this->validator->errors();
		}

		return $response->withRedirect($this->router->pathFor('user.group'));
	}

	//Find group by id
	function delGroup($request, $response, $args)
	{
		$group = new GroupModel($this->db);
		$userGroup = new UserGroupModel($this->db);

		$findGroup = $group->find('id', $args['id']);
		$finduserGroup = $userGroup->findUsers('group_id', $args['id']);
		$pic = $userGroup->findUser('group_id', $args['id'], 'user_id', $_SESSION['login']['id']);
	// var_dump($args['id']);die();
		if ($_SESSION['login']['status'] == 1 || $pic['status'] == 1) {
			$delete = $group->hardDelete($args['id']);

			$this->flash->addMessage('succes', 'Grup telah berhasil dihapus');
		} else {
			$this->flash->addMessage('error', 'Anda tidak memiliki akses untuk menghapus grup ini!');
		}
			return $response->withRedirect($this->router
					->pathFor('user.group'));
	}

	public function searchGroup($request, $response)
    {
        $group = new GroupModel($this->db);

        $search = $request->getParams()['search'];
        $userId  = $_SESSION['login']['id'];

        // $data['search'] = $request->getQueryParam('search');
		$data['groups'] =  $group->search($search);
        $data['count'] = count($data['groups']);
        // var_dump($data);die();
        // $_SESSION['search'] = $data;

        return $this->view->render($response, 'users/user/found-group.twig', $data);
    }

	// Set user as member of group
	public function joinGroup($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);

		$userId =$_SESSION['login']['id'];

		$findUser = $userGroup->finds('user_id', $userId, 'group_id', $args['id']);

		$data = [
			'group_id' 	=> 	$args['id'],
			'user_id'	=>	$userId,
		];

		if ($findUser[0]) {
			$this->flash->addMessage('error', 'Anda sudah tergabung ke grup!');
		} else {
			$addMember = $userGroup->createData($data);

			$this->flash->addMessage('succes', 'Anda berhasil bergabung dengan grup');
		}

		return $response->withRedirect($this->router
		->pathFor('user.group'));
	}

	public function leaveGroup($request, $response, $args)
	{
		$userGroup = new UserGroupModel($this->db);
		$posts = new \App\Models\PostModel($this->db);

		$userId = $_SESSION['login']['id'];

		$group = $userGroup->finds('user_id', $userId, 'group_id', $args['id']);
		$findPost = $posts->finds('creator', $userId, 'group_id', $args['id']);

		if ($group[0]) {

			if ($findPost) {
				foreach ($findPost as $key => $value) {
					$post_del = $posts->hardDelete($value['id']);
				}
			}

			$leaveGroup = $userGroup->hardDelete($group[0]['id']);

			$data = $this->responseDetail(200, 'Succes', 'Anda telah meninggalkan grup');
		} else {
			$this->flash->addMessage(400, 'Error', 'Anda tidak tergabung di grup ini!');

		}

		return $data;
	}

}

?>
