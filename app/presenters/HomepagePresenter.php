<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class HomepagePresenter extends BasePresenter
{


    public function startup()
    {

        parent::startup();

    }

    public function renderDefault()
    {
        $this->template->products = \dibi::query('SELECT CONCAT(SUBSTRING(p.popis, 1, 100)," ...") AS popis, p.nazev,p.cena,p.thumbnail AS obrazek,p.skladem,p.id AS product_id,k.nazev AS kategorie_produktu FROM produkt p left join kategorie k ON p.kategorie_id=k.id ORDER BY rand() LIMIT 3')->fetchAll();
        $this->template->categories = \dibi::query('SELECT * FROM kategorie')->fetchAll();

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
