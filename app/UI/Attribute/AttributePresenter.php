<?php
namespace App\UI\Attribute;

use App\Model\AttributeManager;
use Nette;
use Nette\Application\UI\Form;

final class AttributePresenter extends Nette\Application\UI\Presenter
{
    private AttributeManager $attributeManager;

    public function __construct(AttributeManager $attributeManager)
    {
        parent::__construct();
        $this->attributeManager = $attributeManager;
    }

    public function renderDefault(): void
    {
        $this->template->attributes = $this->attributeManager->getAllAttributes();
    }

    protected function createComponentAttributeForm(): Form
    {
        $form = new Form;

        $form->addText('name', 'Názov atribútu:')
            ->setRequired('Zadajte názov atribútu.');
        $form->addText('type', 'Typ atribútu:')
            ->setRequired('Zadajte typ atribútu (napr. text, číslo, dátum).');
        $form->addCheckbox('isRequired', 'Povinný');
        $form->addSubmit('save', 'Pridať atribút');

        $form->onSuccess[] = [$this, 'processAttributeForm'];
        return $form;
    }

    public function processAttributeForm(Form $form, array $values): void
    {
        $this->attributeManager->createAttribute($values['name'], $values['type'], $values['isRequired']);
        $this->flashMessage('Atribút bol úspešne pridaný!', 'success');
        $this->redirect('this');
    }

    public function handleDelete(int $id): void
    {
        $this->attributeManager->deleteAttribute($id);
        $this->flashMessage('Atribút bol úspešne vymazaný!', 'success');
        $this->redirect('this');
    }
}
