<?php
/**
 * Created by PhpStorm.
 * User: Tomkour
 * Date: 02.04.2018
 * Time: 0:57
 */

namespace App\Presenters;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Dibi;


class OrderPresenter extends BasePresenter
{
    public function renderDefault()
    {
        $this->template->orders = \dibi::query('SELECT * FROM objednavky WHERE username=?',$_SESSION['username']);
    }

    public function actionCancelOrder($id)
    {
        \dibi::query('UPDATE objednavky SET stav_objednavky=2 WHERE id=?',$id);
        $email = \dibi::query('SELECT email FROM objednavky WHERE username=?',$_SESSION['username'])->fetchSingle();
        $mail = new Message;
        $mail->setFrom('Tomáš Stehlík <stehlik.t@centrum.cz>')
            ->addTo($email)
            ->setSubject('Zrušení objednávky')
            ->setBody("Dobrý den,\nvaše objednávka byla úspěšně zrušena.\nDěkujeme a přejeme hezký zbytek dne.");

        $mailer = new SendmailMailer;
        $mailer->send($mail);
        $this->redirect('Order:default');
    }

    public function renderDetail($order_id)
    {
        $this->template->order_products = \dibi::query('SELECT p.id,p.nazev,o.quantity FROM pro_obj o LEFT JOIN produkt p ON(o.id_produktu=p.id) WHERE o.id_objednavky=?',$order_id);
    }

}