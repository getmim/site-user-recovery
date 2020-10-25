<?php
/**
 * RecoveryController
 * @package site-user-recovery
 * @version 0.0.1
 */

namespace SiteUserRecovery\Controller;

use SiteUserRecovery\Library\Meta;
use LibForm\Library\Form;
use LibUserRecovery\Model\UserRecovery as URecovery;
use LibUser\Library\Fetcher;

class RecoveryController extends \Site\Controller
{
	public function recoveryAction() {
        $next = $this->req->getQuery('next');
        if(!$next)
            $next = $this->router->to('siteHome');

        if($this->user->isLogin())
            return $this->res->redirect($next);

        $form = new Form('site.me.recovery');

        $params = [
            '_meta' => [
                'title' => 'Recovery Account'
            ],
            'form'  => $form,
            'meta' => Meta::recovery(),
            'error' => []
        ];

        if(!($valid = $form->validate()) || !$form->csrfTest('noob')){
            $params['error'] = true;
            $this->res->render('me/recovery/recovery', $params);
            return $this->res->send();
        }

        $user = Fetcher::getOne(['name'=>$valid->identity]);
        if(!$user){
        	if(module_exists('lib-user-main-email'))
        		$user = Fetcher::getOne(['email'=>$valid->identity]);
        	if(!$user && module_exists('lib-user-main-phone'))
        		$user = Fetcher::getOne(['phone'=>$valid->identity]);
        }

        if(!$user){
        	$params['error'] = true;
            $this->res->render('me/recovery/recovery', $params);
            return $this->res->send();
        }

        // create recovery object
        $verif = [
            'user'    => $user->id,
            'expires' => date('Y-m-d H:i:s', strtotime('+2 hour')),
            'hash'    => ''
        ];

        while(true){
            $verif['hash'] = md5(time() . '-' . uniqid() . '-' . $user->id);
            if(!URecovery::getOne(['hash'=>$verif['hash']]))
                break;
        }
        URecovery::create($verif);

        $params['reset_url'] = $this->router->to('siteMeRecoveryReset', ['hash'=>$verif['hash']], ['next'=>$next]);

        $this->res->render('me/recovery/recovery-success', $params);
        return $this->res->send();
    }

    public function resentAction(){
        $next = $this->req->getQuery('next');
        if(!$next)
            $next = $this->router->to('siteHome');

        if($this->user->isLogin())
            return $this->res->redirect($next);

        $user_id = $this->req->param->user;
        $recover_id = $this->req->param->recover;

        $user = Fetcher::getOne(['id'=>$user_id]);
        if(!$user)
            return $this->show404();
        $recover = URecovery::getOne(['id'=>$recover_id, 'user'=>$user_id]);
        if(!$recover)
            return $this->show404();

        $params = [
            '_meta' => [
                'title' => 'Recovery Account'
            ],
            'meta'  => Meta::recovery(),
            'next'  => $next,
            'user'  => $user,
            'recover'=> $recover
        ];

        $params['reset_url'] = $this->router->to('siteMeRecoveryReset', ['hash'=>$recover->hash], ['next'=>$next]);

        $this->res->render('me/recovery/recovery-success', $params);
        return $this->res->send();
    }

    public function resetAction(){
    	$next = $this->req->getQuery('next');
        if(!$next)
            $next = $this->router->to('siteHome');

        if($this->user->isLogin())
            return $this->res->redirect($next);

        $hash = $this->req->param->hash;
        $recovery = URecovery::getOne(['hash'=>$hash]);
        if(!$recovery)
        	return $this->show404();

        $expire = strtotime($recovery->expires);
        if($expire < time()){
            URecovery::remove(['id'=>$recovery->id]);
            return $this->show404();
        }

        $form = new Form('site.me.reset');

        $params = [
            '_meta' => [
                'title' => 'Reset Password Account'
            ],
            'form'  => $form,
            'meta' => Meta::reset(),
            'errors' => []
        ];

        if(!($valid = $form->validate()) || !$form->csrfTest('noob')){
            $params['errors'] = $form->getErrors();
            $this->res->render('me/recovery/reset', $params);
            return $this->res->send();
        }

        $new_password = $this->user->hashPassword($valid->password);

        Fetcher::set(['password'=>$new_password], ['id'=>$recovery->user]);

        URecovery::remove(['id'=>$recovery->id]);

        $this->res->render('me/recovery/reset-success', $params);
        return $this->res->send();
    }
}