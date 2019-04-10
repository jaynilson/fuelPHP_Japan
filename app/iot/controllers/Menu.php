<?php
class MenuController extends Base\ViewController
{
    /**
     * 
     * @var MenuModel
     */
    protected $model;

    protected function renderForm(): void
    {
        parent::renderForm();
        $this->form['fields']['data'][0]['form']['input']['parent'] = [
            'type' => 'select',
            'name' => 'parent',
            'label' => l($this->_request->controller.'.parent'),
            'lang' => false,
            'values' => [['options' => $this->model->getSelect()]]
        ];
    }
}