<?php

namespace App\AdminModule\Presenters;

class AdminPresenter extends BasePresenter
{
    public function startup() {
        parent::startup();

        if (!$this->user->isLoggedIn() || $_SESSION['permission']!=1)
        {
            $this->redirect(':Homepage:default');
        }
    }
}