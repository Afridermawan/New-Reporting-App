<?php
$app->group('/api', function() use ($app, $container) {
    $app->get('/', 'App\Controllers\api\HomeController:index');
    $app->post('/login', 'App\Controllers\api\UserController:login')
        ->setname('api.user.login');
});
$app->group('/api/group', function() use ($app, $container) {
	$app->post('/create', 'App\Controllers\api\GroupController:add')
        ->setName('api.group.add');

    $app->post('/pic/delete/user/{group}/{id}', 'App\Controllers\api\GroupController:deleteUser')
        ->setName('api.delete.user.group');

    $app->put('/edit/{id}', 'App\Controllers\api\GroupController:update')
        ->setName('api.group.update');

    $app->get('/list', 'App\Controllers\api\GroupController:index')
        ->setName('api.group.list');

    $app->get('/detail/{id}', 'App\Controllers\api\GroupController:findGroup')
        ->setName('api.group.detail');

    $app->delete('/delete/{id}', 'App\Controllers\api\GroupController:delete')
        ->setName('api.group.delete');

    $app->post('/add/user', 'App\Controllers\api\GroupController:setUserGroup')
        ->setName('api.user.add.group');

    $app->put('/set/guardian/{id}/{group}', 'App\Controllers\api\GroupController:setAsGuardian')
        ->setName('api.user.set.guardian');

    $app->put('/set/pic/{group}/{id}', 'App\Controllers\api\GroupController:setAsPic')
        ->setName('api.user.set.pic');

    $app->put('/set/member/{group}/{id}', 'App\Controllers\api\GroupController:setAsMember')
        ->setName('api.user.set.member');

    $app->get('/detail', 'App\Controllers\api\GroupController:getGroup')
        ->setName('api.getGroup');

    $app->get('/{id}/del', 'App\Controllers\api\GroupController:delGroup')
        ->setName('api.delGroup');

    $app->get('/{id}/leave', 'App\Controllers\api\GroupController:leaveGroup')
        ->setName('api.group.leave');

    $app->get('/join/{id}', 'App\Controllers\api\GroupController:joinGroup')
        ->setName('api.join.group');

    $app->post('/search', 'App\Controllers\api\GroupController:searchGroup')
        ->setName('api.search.group');

    $app->get('/active', 'App\Controllers\api\GroupController:inActive')
        ->setName('api.group.inactive');

    $app->post('/change/photo/{id}', 'App\Controllers\api\GroupController:postImage')
        ->setName('api.change.photo.group');

    $app->get('/PIC', 'App\Controllers\api\GroupController:getPicGroup')
        ->setName('api.getPicGroup');

    $app->post('/softdelete/{id}', 'App\Controllers\api\GroupController:setInActive')
        ->setName('api.softdelete.group');

    $app->post('/restore/{id}', 'App\Controllers\api\GroupController:restore')
        ->setName('api.restore.group');

    $app->get('/getPic', 'App\Controllers\api\GroupController:getPic')
        ->setName('api.getPic');

    $app->get('/{id}/getusers', 'App\Controllers\api\GroupController:getMemberGroup')
        ->setName('api.getMemberGroup');

    $app->post('/pic/create', 'App\Controllers\api\GroupController:createByUser')
        ->setName('pic.create.group');

    $app->get('/{id}/notMember', 'App\Controllers\api\GroupController:getNotMember')
        ->setName('api.getNotMember');

    $app->post('/pic/addusers', 'App\Controllers\api\GroupController:setMemberGroup')
        ->setName('pic.member.group.set');

    $app->put('/upload/image', 'App\Controllers\api\FileSystemController:upload')
        ->setName('api.upload.image');

    $app->get('/{group}/{id}', 'App\Controllers\api\GroupController:getUserGroup')
        ->setName('api.get.user.group');

    $app->get('/{group}/allusers', 'App\Controllers\api\GroupController:getAllUserGroup')
        ->setName('api.getAllUserGroup');
});