<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Security as NS;



class RegisterPresenter extends BasePresenter
{


    protected function startup()
    {

        parent::startup();
    }

    protected function createComponentRegistrationForm() {
        $form = new \Nette\Application\UI\Form;

        $form->addText('username')
            ->setAttribute('placeholder', 'Přihlašovací jméno')
            ->addRule($form::MIN_LENGTH, 'Přihlašovací jméno musí mít alespoň %d znaky', 3)
            ->setRequired('Zadejte přihlašovací jméno');
        $form->addPassword('password')
            ->setAttribute('placeholder', 'Heslo')
            ->setRequired('Zadejte heslo');
        $form->addText('email')
            ->setType('email')
            ->setAttribute('placeholder', 'E-mail')
            ->addRule($form::EMAIL, 'Zadejte e-mail ve formátu jméno@doména.cz')
            ->setRequired('Zadejte e-mail');

        $form->addSubmit('submit', 'Registrovat');

        $form->addProtection('Vypršel časový limit, odešlete prosím formulář znovu');

        $form->onSuccess[] = [$this, 'registrationFormSucceeded'];
        return $form;
    }

    public function RegistrationFormSucceeded($form, $values){

        $user = new Model\User();
        if(!$user->checkUsername($values->username)){
            $password = NS\Passwords::hash($values->password);

            //nastavení userId podle posledního vloženého id
            $lastId = \dibi::getInsertId($user->insertUser(['username' => $values->username,
                'email' => $values->email,
                'password' => $password
            ]));

            $this->flashMessage("Registrace proběhla v pořádku.");
            $this->redirect("Sign:in");

        }else{

            $this->flashMessage("Uživatelské jméno je již obsazeno.");
        }

    }
}
