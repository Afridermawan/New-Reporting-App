<?php

namespace App\Controllers\web;

class HomeController extends BaseController
{
    public function index($request, $response)
    {
        $article = new \App\Models\ArticleModel($this->db);
        $group = new \App\Models\GroupModel($this->db);
        $item = new \App\Models\Item($this->db);
        $user = new \App\Models\Users\UserModel($this->db);

        if ($_SESSION['login']['status'] == 1) {

            $activeGroup = count($group->getAll());
            $activeUser = count($user->getAll());
            $activeArticle = count($article->getAll());
            $activeItem = count($item->getAll());
            $inActiveGroup = count($group->getAllTrash());
            $inActiveUser = count($user->getAllTrash());
            $inActiveArticle = count($article->getAllTrash());
            $inActiveItem = count($item->getAllTrash());

            $data = $this->view->render($response, 'users/home.twig', [
    			'counts'=> [
                    'group'         =>	$activeGroup,
                    'user'	        =>	$activeUser,
                    'article'	    =>	$activeArticle,
                    'item' 			=>	$activeItem,
                    'inact_group'	=>	$inActiveGroup,
                    'inact_user'	=>	$inActiveUser,
                    'inact_article' =>	$inActiveArticle,
                    'inact_item'	=>	$inActiveItem,
    			]
    		]);

        } elseif ($_SESSION['login']['status'] == 2) {
            $allArticle = count($article->getAll());
            $search = $request->getQueryParam('search');

            $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');

            if (!empty($search)) {
                $findAll = $article->search($request->getQueryParam('search'));
            } else {
                $findAll = $article->getArticle()->setPaginate($page, 3);
            }
// var_dump($findAll);die();
            $data = $this->view->render($response, 'users/home.twig', ['items' => $item->getAll()]);
        }

        return $data;
    }
}
