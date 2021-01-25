<?php
/**
 * Created by PhpStorm.
 * User: Tomkour
 * Date: 09.03.2018
 * Time: 15:13
 */

namespace App\AdminModule\Presenters;

use Nette;
use Dibi;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Http\FileUpload;
use Nette\Utils\Image;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class PagePresenter extends AdminPresenter
{

    private $product;

    public function renderProducts()
    {
        $this->template->products = \dibi::query('SELECT p.id as id,p.nazev,p.popis,p.cena,p.thumbnail AS obrazek,p.id AS product_id,p.skladem,k.nazev AS kategorie_produktu FROM produkt p left join kategorie k ON p.kategorie_id=k.id')->fetchAll();
    }

    public function actionDeleteProduct($product_id)
    {
        \dibi::query('DELETE FROM pro_obj WHERE id_produktu=?',$product_id);
        \dibi::query('DELETE FROM produkt WHERE id=?',$product_id);
        $this->redirect('Page:products');
    }

    protected function createComponentAddNewProductForm()
    {
        $form = new Form();

        $val = array();
        $category = \Dibi::query('SELECT * FROM kategorie');
        foreach ($category as $key => $value) {
            $val += [$value->id => $value->nazev];
        }

        $form->addText('name','Název:')
            ->setRequired();

        $form->addTextArea('description','Popisek:')
            ->setRequired();

        $form->addText('price','Cena:')
            ->setRequired();

        $form->addSelect('category', 'Kategorie:', $val)
            ->setRequired();

        $form->addMultiUpload('obrazek', 'Nahledovy obrázek:')
            ->setRequired();

        $form->addSubmit('send', 'Přidat nový produkt');

        $form->onSuccess[] = array($this, 'addNewProductFormSucceeded');

        return $form;

    }

    public function addNewProductFormSucceeded($form, $values)
    {

        $path = $_SERVER['DOCUMENT_ROOT'];
        $url = "/images/products/$values->category/";
        $val = [
            'nazev' => $values->name,
            'popis' => $values->description,
            'cena' => $values->price,
            'kategorie_id' => $values->category,
            'skladem' => 1
        ];

        \dibi::query('INSERT INTO [produkt]', $val);
        $id = \dibi::getInsertId('produkt');


        $images = array();
        $i = 0;
        $file_ary = $this->reArrayFiles($_FILES['obrazek']);
        foreach ($file_ary as $file) {
           $images[$i]=$file;
           $i++;
            $val = [
                'image' => $url . $file['name'],
                'produkt' => $id
            ];
            move_uploaded_file($file['tmp_name'], $path . $url . $file['name']);
            \dibi::query('INSERT INTO [gallery]', $val);

        }
        $thumb = "thumb_" . $images[0]['name'];
        $this->makeThumb($path . $url . $images[0]['name'], $path . $url . $thumb, 500);

        \dibi::query('UPDATE produkt SET thumbnail=? WHERE id=?',$url.$thumb,$id);

        $this->redirect('Page:default');



    }

    private function reArrayFiles(&$file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }

    public function actionEditProduct($product_id)
    {
        $this->product= \dibi::query('SELECT * FROM produkt WHERE id=?',$product_id)->fetchAll();

    }

    protected function createComponentEditProductForm()
    {
        $form = new Form();

        $val = array();
        $category = \Dibi::query('SELECT * FROM kategorie');
        foreach ($category as $key => $value) {
            $val += [$value->id => $value->nazev];
        }

        $form->addText('name','Název:')
            ->setDefaultValue($this->product[0]['nazev']);

        $form->addTextArea('description','Popisek:')
            ->setDefaultValue($this->product[0]['popis']);

        $form->addText('price','Cena:')
            ->setDefaultValue($this->product[0]['cena']);

        $form->addSelect('stock', 'Skladnost', [
            '1' => 'Skladem',
            '0' => 'Vyprodáno',
        ]);
        $form['stock']->setDefaultValue($this->product[0]['skladem']);

       $form->addSelect('category', 'Kategorie:', $val)
            ->setDefaultValue($this->product[0]['kategorie_id']);

        $form->addSubmit('send','Odeslat');

        $form->onSuccess[] = array($this, 'editProductFormSucceeded');

        return $form;
    }

    public function editProductFormSucceeded($form, $values)
    {
        \dibi::query('UPDATE produkt set',
            [
                'nazev' => $values->name,
                'popis' => $values->description,
                'cena'  => $values->price,
                'skladem' => $values->stock,
                'kategorie_id' => $values->category,
            ], 'WHERE id=?',$this->getParameter('product_id'));

        $this->redirect('Page:products');
        $this->flashMessage('Úprava proběhla v pořádku');
    }

    public function renderUserManagment()
    {
        $this->template->users = \dibi::query('SELECT * FROM users')->fetchAll();

    }

    public function actionDeleteUser($id)
    {
        \dibi::query('DELETE FROM users WHERE id=?',$id);
        $this->redirect('Page:userManagment');
    }

    public function actionAssignAdminPermission($id)
    {
        \dibi::query('UPDATE users SET permission_id=1 WHERE id=?',$id);
        $this->redirect('Page:userManagment');
    }

    public function actionRemoveAdminPermission($id)
    {
        \dibi::query('UPDATE users SET permission_id=0 WHERE id=?',$id);
        $this->redirect('Page:userManagment');
    }

    private function makeThumb($src, $dest, $desired_width)
    {


        $source_image = imagecreatefromjpeg($src);
        $width = imagesx($source_image);
        $height = imagesy($source_image);


        $desired_height = floor($height * ($desired_width / $width));


        $virtual_image = imagecreatetruecolor($desired_width, $desired_height);


        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);


        imagejpeg($virtual_image, $dest);

    }
    public function renderOrders()
    {
        $this->template->orders = \dibi::query('SELECT * FROM objednavky');
    }

    public function actionCompleteOrder($id)
    {
        \dibi::query('UPDATE objednavky SET stav_objednavky=1 WHERE id=?',$id);
        $email = \dibi::query('SELECT email FROM objednavky WHERE id=?',$id)->fetchSingle();
        $mail = new Message;
        $mail->setFrom('Tomáš Stehlík <stehlik.t@centrum.cz>')
            ->addTo($email)
            ->setSubject('Vyřízení objednávky')
            ->setBody("Dobrý den,\nvaše objednávka byla schávela a vyřízena.\nDoručení očekávejte do 5ti pracovních dnů.\nDěkujeme, že jste si pro nákup vybrali náš E-shop!\nDěkujeme a přejeme hezký zbytek dne.");

        $mailer = new SendmailMailer;
        $mailer->send($mail);
        $this->redirect('Page:orders');
    }

    public function actionCancelOrder($id)
    {
        dibi::query('UPDATE objednavky SET stav_objednavky=2 WHERE id=?',$id);
        $email = \dibi::query('SELECT email FROM objednavky WHERE id=?',$id)->fetchSingle();
        $mail = new Message;
        $mail->setFrom('Tomáš Stehlík <stehlik.t@centrum.cz>')
            ->addTo($email)
            ->setSubject('Zrušení objednávky')
            ->setBody("Dobrý den,\nvaše objednávka byla zrušena.\nDěkujeme a přejeme hezký zbytek dne.");

        $mailer = new SendmailMailer;
        $mailer->send($mail);
        $this->redirect('Page:orders');
        $this->redirect('Page:orders');
    }

    public function renderDetailOrder($order_id)
    {
        $this->template->order_products = \dibi::query('SELECT p.id,p.nazev,o.quantity FROM pro_obj o LEFT JOIN produkt p ON(o.id_produktu=p.id) WHERE o.id_objednavky=?',$order_id);
    }
}