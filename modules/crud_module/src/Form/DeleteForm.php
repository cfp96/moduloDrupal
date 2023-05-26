<?php

namespace Drupal\crud_module\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Formulario de confirmación para eliminar un registro.
 */
class DeleteForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'crud_module_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('¿Estás seguro de que deseas eliminar este usuario?');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Esta acción no se puede deshacer.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Eliminar');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('crud_module.show_data');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $id,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getValue('id');

    $connection = Database::getConnection();
    $connection->delete('example_users')
      ->condition('id', $id)
      ->execute();

    $form_state->setRedirect('crud_module.show_data');
  }

}
