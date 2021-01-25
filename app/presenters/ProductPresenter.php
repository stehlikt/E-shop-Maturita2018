<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class ProductPresenter extends BasePresenter
{
    public function startup()
    {
        parent::startup();
        $this->template->categories = \dibi::query('SELECT * FROM kategorie')->fetchAll();
    }

    public function renderDefault($productId)
    {
        $product =\dibi::query('SELECT p.nazev,p.popis,p.cena,p.thumbnail AS obrazek,p.id AS product_id,p.skladem,k.nazev AS kategorie_produktu FROM produkt p left join kategorie k ON p.kategorie_id=k.id WHERE p.id=?',$productId)->fetchAll();
        $this->template->product = $product[0];
        $this->template->reviews = \dibi::query('SELECT *,DATE_FORMAT(datum, \'%d-%m-%Y\') as DATUM_RECENZE FROM  recenze WHERE produkt_id=? ORDER BY datum DESC',$productId)->fetchAll();
        $this->template->images = \dibi::query('SELECT image FROM gallery WHERE produkt=?',$productId)->fetchAll();
    }

    public function renderProductsByCategory($categoryId)
    {
        $this->template->products = \dibi::query('SELECT CONCAT(SUBSTRING(p.popis, 1, 100)," ...") AS popis, p.nazev,p.cena,p.thumbnail AS obrazek,p.id AS product_id,k.nazev AS kategorie_produktu FROM produkt p left join kategorie k ON p.kategorie_id=k.id WHERE kategorie_id=?',$categoryId)->fetchAll();
    }

    protected function createComponentReviewForm()
    {
        $form = new Form;

        $form->addText('content', 'Recenze:')
            ->setRequired();

        $form->addSubmit('send', 'Publikovat Recenzi');

        $form->onSuccess[] = [$this, 'reviewFormSucceeded'];

        return $form;
    }

    public function reviewFormSucceeded($form, $values)
    {
        $productId = $this->getParameter('productId');

        \dibi::query('INSERT INTO recenze',[
            'produkt_id' => $productId,
            'user' => $_SESSION['username'],
            'recenze' => $values->content,
        ]);

        $this->redirect('this');
    }


    public function createComponentAddToCartForm()
    {
        $form = new Form;
        $form->addHidden("id", 'id');
        $form->addHidden('price','price');
        $form->addText('quantity', 'Počet');
        $form->addSubmit('send');

        $form->onSuccess[] = [$this, "addToCartFormSucceeded"];

        return $form;
    }

    public function addToCartFormSucceeded($form, $values)
    {
        $_SESSION["shoppingCart"][$values->id] = [
            "productId" => $values->id,
            "quantity" => $values->quantity,
            "price" => $values->price*$values->quantity
        ];
        $this->redirect("Homepage:default");

    }
}
