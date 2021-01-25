<?php
/**
 * Created by PhpStorm.
 * User: Tomkour
 * Date: 09.03.2018
 * Time: 15:10
 */

namespace App\AdminModule\Presenters;

use Nette;
use App\Model;


class BasePresenter extends Nette\Application\UI\Presenter
{
    public function startup()
    {
        parent::startup();
        $this->setLayout('admin');
    }
}