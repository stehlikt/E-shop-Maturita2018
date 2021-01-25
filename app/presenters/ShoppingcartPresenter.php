<?php
/**
 * Created by PhpStorm.
 * User: Tomkour
 * Date: 06.03.2018
 * Time: 19:16
 */

namespace App\Presenters;

use Nette;
use App\Model;
use Dibi;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;


class ShoppingcartPresenter extends BasePresenter
{
    private $mailer;
    public function renderDefault()
    {
        if (isset($_SESSION['shoppingCart'])) {
            $this->getAllItems();
            $price = 0;
            foreach ($_SESSION["shoppingCart"] as $value => $product) {
                $price = $price + $product['price'];
            }
            $this->template->price = $price;
        }
    }

    public function getAllItems()
    {

        foreach ($_SESSION['shoppingCart'] as $row) {

            //TODO ošetřit sčítání počtu kusů u produktu se strejným productId
            $productId = array();
            $cartProducts[] = [
                "productName" => \dibi::query("SELECT nazev FROM produkt WHERE id=?", $row["productId"])->fetchSingle(),
                "price" => \dibi::query("SELECT cena FROM produkt WHERE id=?", $row["productId"])->fetchSingle(),
                "productId" => $row["productId"],
                "quantity" => $row["quantity"]
            ];
            $productId[] = $row["productId"];

        }
        $this->template->cartProducts = $cartProducts;
    }

    public function createComponentRemoveProductForm()
    {

        $form = new Form();
        $form->addHidden("id", 'id');
        $form->addSubmit('send', 'ODEBRAT Z KOŚÍKU');

        $form->onSuccess[] = [$this, "RemoveProductSucceeded"];

        return $form;

    }

    public function removeProductSucceeded($form, $values)
    {
        unset($_SESSION['shoppingCart'][$values->id]);
        if ($_SESSION['shoppingCart'] == NULL) {
            $this->redirect('Homepage:default');
        }


    }

    protected function createComponentSendOrderForm()
    {
        $form = new Form;

        $form->addText('name', 'Jméno:')
            ->setRequired('Zadejte prosím své jméno.');

        $form->addText('surname', 'Příjmení:')
            ->setRequired('Zadejte prosím své příjmení.');

        $form->addText('street', 'Ulice,Č.p:')
            ->setRequired('Zadejte prosím svou ulici.');

        $form->addText('city', 'Město:')
            ->setRequired('Zadejte prosím své město.');

        $form->addText('psc', 'PSČ:')
            ->setRequired('Zadejte prosím své PSČ.');

        $form->addText('phone', 'Telefon:')
            ->setRequired('Zadejte prosím své telefoní číslo.');

        $form->addText('email', 'Email:')
            ->setRequired('Zadejte prosím svůj email.');

        $form->addSelect('transport', 'Doprava', [
            '0' => 'Česká pošta',
            '1' => 'PPL',
            '2' => 'Kurýr'
        ]);

        $form->addSelect('payment', 'platba', [
            '0' => 'Platební kartou',
            '1' => 'Dobírka',
            '2' => 'Převodem z účtu'
        ]);

        $form->addSubmit('send', 'Odeslat Objednávku');

        $form->onSuccess[] = [$this, 'sendOrderFormSucceeded'];

        return $form;
    }

    public function sendOrderFormSucceeded($form, $values)
    {

        $mail = new Message;
        $mail->setFrom('Tomáš Stehlík <stehlik.t@centrum.cz>')
            ->addTo($values->email)
            ->setSubject('Potvrzení objednávky')
            ->setBody("Dobrý den,\nvaše objednávka byla přijata. Během několika minut vaši objednávku vyřídíme a kontaktujeme skrze email.\nDěkujeme, že jste si pro nákup vybrali náš E-shop!.\ Děkujeme a přejeme hezký zbytek dne.");

        $mailer = new SendmailMailer;
        $mailer->send($mail);

        $price = 0;
        foreach ($_SESSION["shoppingCart"] as $value => $product) {
            $price = $price + $product['price'];
        }

        $val = [
            'username' => $_SESSION['username'],
            'email' => $values->email,
            'jmeno' => $values->name,
            'prijmeni' => $values->surname,
            'ulice' => $values->street,
            'mesto' => $values->city,
            'psc' => $values->psc,
            'telefon' => $values->phone,
            'doprava' => $values->transport,
            'typ_platby' => $values->payment,
            'cena' => $price,
            'stav_objednavky' => 0
        ];

        \dibi::query('INSERT INTO objednavky', $val);

        $id = \dibi::getInsertId('objednavky');
        foreach ($_SESSION['shoppingCart'] as $item) {

            $val = [
                'id_produktu' => $item['productId'],
                'quantity' => $item['quantity'],
                'id_objednavky' => $id
            ];
            \dibi::query('INSERT INTO pro_obj', $val);
        }


        unset($_SESSION['shoppingCart']);
        $this->redirect('Shoppingcart:thankYou');


    }


}