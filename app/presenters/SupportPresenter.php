<?php
/**
 * Created by PhpStorm.
 * User: Tomkour
 * Date: 02.04.2018
 * Time: 1:08
 */

namespace App\Presenters;
use Nette;
use App\Model;
use Dibi;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class SupportPresenter extends BasePresenter
{
    protected function createComponentSupportMessageForm()
    {
        $form = new Form();

        $form->addText("email", 'Email')
            ->setRequired('Zadejte prosím svůj email.');

        $form->addTextArea('message','Zpráva:')
            ->setRequired('Musíte vyplnit svůj dotaz');
        $form->addSubmit('send', 'Odeslat');

        $form->onSuccess[] = [$this, "supportMessageSucceeded"];

        return $form;
    }

    public function supportMessageSucceeded($form, $values)
    {
        $mail = new Message;
        $mail->setFrom($values->email)
            ->addTo('stehlik.t@centrum.cz')
            ->setSubject('Dotaz na technickou podporu')
            ->setBody($values->message);

        $mailer = new SendmailMailer;
        $mailer->send($mail);
        $this->redirect('Homepage:default');

    }
}