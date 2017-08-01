<?php   

namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Exception\BadResponseException as GuzzleException;

class GroupController extends BaseController
{
	//Get All Group
	function index(Request $request, Response $response)
	{
		$client = $this->client->request('GET', 
					$this->router->pathFor('api.group.list'));
		$content = json_decode($client->getBody()->getContents());

		return $this->view->render($response, 'admin/group/index.twig', $content->reporting); 
	}

	//Get inactive group
	function inActive($request, $response)
	{
		try {
			$client = $this->client->request('GET', 
						$this->router->pathFor('api.group.inactive'));
			$content = json_decode($client->getBody()->getContents());			
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody());
			$this->flash->addMessage('errors', 'Data tidak ditemukan');
		}

		return $this->view->render($response, 'admin/group/inactive.twig', $content->reporting); 

	}

	//Find group by id
	function findGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET', 
						$this->router->pathFor('api.group.detail', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage('errors', 'Data tidak ditemukan');
		}

		return $this->view->render($response, 'admin/group/detail.twig', $content->reporting); 
	}

	//Get create group
	public function getAdd($request, $response)
	{
		return $this->view->render($response, 'admin/group/add.twig');
	}

	//Post create group
	public function add($request, $response)
	{
			$data = [
				'name' 			=>	$request->getParams()['name'],
				'description'	=>	$request->getParams()['description'],
				'image'			=>	$request->getParams()['image'],
			];
		try {
			$client = $this->client->request('POST', 
						$this->router->pathFor('api.group.add'), [

						'json' => $data
			]);
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$content = $e->getResponse()->getBody()->getContents();
			$content = json_decode($content);

			$this->flash->addMessage('errors', 'Data tidak ditemukan');
		}

		return $this->view->render($response, 'admin/group/detail.twig', $content->reporting); 
	}

	//Get edit group
	public function getUpdate($request, $response, $args)
	{
		$group = new GroupModel($this->db);
        $data['group'] = $group->find('id', $args['id']);
		return $this->view->render($response, 'admin/group/edit.twig', $data);
	}

	//Edit group
	public function update($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET', 
						$this->router->pathFor('api.group.update', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($client->getResponse()->getBody()->getContents());
		}
	}

	//Set inactive/soft delete group
	public function setInactive($request, $response, $args)
	{
		try {
			$client = $this->client->request('POST', 
						$this->router->pathFor('api.softdelete.group', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());

            $this->flash->addMessage('success', 'Berhasil menghapus data');
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
            $this->flash->addMessage('errors', 'Data tidak ditemukan');
		}

		return $response->withRedirect($this->router->pathFor('group.list'));
	}

	//restore
	public function restore($request, $response, $args)
	{
		try {
			$client = $this->client->request('POST', 
						$this->router->pathFor('api.restore.group', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
            $this->flash->addMessage('errors', 'Data tidak ditemukan');
		}

		return $response->withRedirect($this->router->pathFor('group.list'));
	}

	//Set user as member or PIC of group
	public function setUserGroup($request, $response)
	{
		$data = [
				'group_id' 			=>	$request->getParams()['group_id'],
				'user_id'			=>	$request->getParams()['user_id']
			];
		try {
			$client = $this->client->request('POST',
						$this->router->pathFor('api.user.add.group'), [
					'json' => $data
			]);
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}

		return $this->router->pathFor('user.group.get', ['id' => $groupId]);
	}

	//Get all user in group
	public function getMemberGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET', 
						$this->router->pathFor('api.getMemberGroup', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}

		return $this->view->render($response, '', $content->reporting);
	}

	//Get all user in group
	public function getNotMember($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.getNotMember', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}
		return $this->router->pathFor('home');
	}

	//Set user as member of group
	public function setMemberGroup($request, $response, $args)
	{		
			$data = [
				'group_id' 			=>	$request->getParams()['group_id'],
				'user_id'			=>	$request->getParams()['user_id']
			];
		try {
			$client = $this->client->request('POST',
						$this->router->pathFor('pic.member.group.set'), [
					'json' => $data
			]);
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}

		return $this->router->pathFor('user.group.get', ['id' => $groupId]);
	}

	public function getGroup($request, $response)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.getGroup'));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}

		return $this->view->render($response, '', $content->reporting);
	}

	public function getPic($request, $response)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.getPic'));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}

		return $this->view->render($response, '', $content->reporting);
	}

	public function getPicGroup($request, $response)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.getPicGroup'));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
			var_dump($content);die();
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage('error', 'Data tidak ditemukan');

		}
		return $this->view->render($response, '', $content->reporting);
	}

	//Post create group
	public function createByUser($request, $response)
	{
		// $userId  = $_SESSION['login']['id'];

		$dataGroup = [
			'name' 			=>	$request->getParams()['name'],
			'description'	=>	$request->getParams()['description'],
			'image'			=>	$request->getParams()['image'],
		];

		try {
			$client = $this->client->request('POST', 
						$this->router->pathFor('pic.create.group'), [
					'json' => $dataGroup
			]);
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}

		return $response->withRedirect($this->router->pathFor('user.group'));
	}

	//Find group by id
	public function delGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET', 
						$this->router->pathFor('api.delGroup', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());		
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());		
			$this->flash->addMessage(404, 'Data tidak ditemukan');
		}

		return $this->router->render($response, 'user.group', $content->reporting);
	}

	public function searchGroup($request, $response)
    {
		try {
			$client = $this->client->request('GET', 
						$this->router->pathFor('api.search.group'));
			$content = json_decode($client->getBody()->getContents());		
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());		
			$this->flash->addMessage(404, 'Data tidak ditemukan');
		}

        return $this->view->render($response, 'users/user/found-group.twig', $content->reporting);
    }

	public function leaveGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET', 
						$this->router->pathFor('api.group.leave', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());		
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());		
			$this->flash->addMessage(404, 'Data tidak ditemukan');
		}		

		return $this->view->render($response, '', $content->reporting);
	}

	//Delete group
	public function delete($request, $response, $args)
	{
		try {
			$client = $this->client->request('DELETE', 
						$this->router->pathFor('api.group.delete', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());		
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());		
			$this->flash->addMessage(404, 'Data tidak ditemukan');
		}

		return $this->view->render($response, 'admin/group/index.twig', $content->reporting); 
	}

	//Set user as member of group
	public function joinGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET', 
						$this->router->pathFor('api.join.group', ['id' => $args['id']]));
			$content = json_decode($client->getBody());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage(400, 'Anda sudah bergabung dengan group');
		}

		return $this->view->render($response, '', $content->reporting);
	}

	//set As guardian
	public function setAsGuardian($request, $response, $args)
	{
		try {
			$client = $this->client->request('PUT', 
						$this->router->pathFor('api.user.set.guardian', 
								['group' => $args['group'], 'id' => $args['id']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}

		return $this->view->render($response, '', $content->reporting);
	}	

	//set As member
	public function setAsMember($request, $response, $args)
	{
		try {
			$client = $this->client->request('PUT', 
						$this->router->pathFor('api.user.set.member', 
								['id' => $args['id'], 'group' => $args['group']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}

		return $this->view->render($response, '', $content->reporting);
	}

	//set As PIC
	public function setAsPic($request, $response, $args)
	{
		try {
			$client = $this->client->request('PUT', 
						$this->router->pathFor('api.user.set.pic', 
								['id' => $args['id'], 'group' => $args['group']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}

		return $this->view->render($response, '', $content->reporting);
	}

	//delete user
	public function deleteUser($request, $response, $args)
	{
		try {
			$client = $this->client->request('POST', 
						$this->router->pathFor('api.delete.user.group', 
								['group' => $args['group'], 'id' => $args['id']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}

		return $this->view->render($response, '', $content->reporting);
	}

	//delete user
	public function postImage($request, $response, $args)
	{
		try {
			$client = $this->client->request('POST', 
						$this->router->pathFor('api.change.photo.group', 
								['id' => $args['id']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}

		return $this->view->render($response, '', $content->reporting);
	}
}

?>