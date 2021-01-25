<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\components\Authenticator;
use App\Model;


class SignPresenter extends BasePresenter
{

    protected function createComponentSignInForm()
    {
        $form = new \Nette\Application\UI\Form;
        $form->elementPrototype->addAttributes(['class' => 'form-horizontal']);

        $form->addText('username')
            ->setAttribute('placeholder', 'Přihlašovací jméno')
            ->setRequired('Zadejte přihlašovací jméno');
        $form->addPassword('password')
            ->setAttribute('placeholder', 'Heslo')
            ->setRequired('Zadejte heslo');

        $form->addSubmit('submit');

        $form->addProtection('Vypršel časový limit, odešlete prosím formulář znovu');

        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        return $form;
    }

    public function signInFormSucceeded($form, $values)
    {
        $user = new Model\User();
        $data = ['username' => $values->username, 'password' => $values->password];
        try{
            $this->getUser()->login($data);
            $this->getUser()->setExpiration('45 minutes', false);
        }
        catch(\Exception $e){
            $this->flashMessage($e->getMessage());
            $this->redirect('Sign:in');
        }
        $_SESSION['username'] = $values->username;
        $_SESSION['permission'] = $user->getPermission($values->username);
        $this->redirect('Homepage:default');

    }
    public function actionOut()
    {
        $this->getUser()->logout();
        session_unset();
        $this->redirect('Homepage:default');
    }
}
