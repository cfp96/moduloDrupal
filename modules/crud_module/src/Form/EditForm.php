<?php

namespace Drupal\crud_module\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Formulario de edición para un registro existente.
 */
class EditForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'crud_module_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $data = $this->getUserDataById($id);
    $date = $data->fecha_de_nacimiento;

    if (!empty($date)) {
      $timestamp = strtotime($date);
      if ($timestamp === FALSE) {
        $timestamp = strtotime(str_replace('/', '-', $date));
      }
      $date = date('Y-m-d', $timestamp);
    }

    // Agrega los elementos de formulario necesarios para editar los campos.
    $form['nombre'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#default_value' => $data->nombre,
      '#required' => TRUE,
    ];

    $form['identificacion'] = [
      '#type' => 'textfield',
      '#title' => $this->t('identificacion'),
      '#default_value' => $data->identificacion,
      '#required' => TRUE,
    ];

    $form['fecha_de_nacimiento'] = [
      '#type' => 'date',
      '#title' => $this->t('Fecha de nacimiento'),
      '#default_value' => $date,
      '#required' => TRUE,
    ];

    $form['cargo'] = [
      '#type' => 'select',
      '#title' => $this->t('Cargo'),
      '#default_value' => $data->cargo,
      '#options' => [
        'administrador' => 'Administrador',
        'webmaster' => 'Webmaster',
        'desarrollador' => 'Desarrollador',
      ],
      '#required' => TRUE,
    ];

    // Agrega un campo oculto para almacenar el ID del registro.
    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $id,
    ];

    // Agrega un botón de envío para actualizar el registro.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Actualizar'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if ($values['cargo'] == 'administrador') {
      $status = 1;
    }
    else {
      $status = 0;
    }

    $connection = Database::getConnection();
    $query = $connection->update('example_users')
      ->fields([
        'nombre' => $values['nombre'],
        'identificacion' => $values['identificacion'],
        'fecha_de_nacimiento' => $values['fecha_de_nacimiento'],
        'cargo' => $values['cargo'],
        'estado' => $status,
      ])
      ->condition('id', $values['id'])
      ->execute();

    $form_state->setRedirect('crud_module.show_data');
  }

  /**
   * Function to get the data of a user with the id.
   */
  private function getUserDataById($id) {
    $query = \Drupal::database()->select('example_users', 'eu')
      ->fields('eu', ['nombre', 'identificacion', 'fecha_de_nacimiento', 'cargo'])
      ->condition('id', $id)
      ->range(0, 1);

    $result = $query->execute()->fetch();

    if ($result) {
      $data = new \stdClass();
      $data->nombre = $result->nombre;
      $data->identificacion = $result->identificacion;
      $data->fecha_de_nacimiento = $result->fecha_de_nacimiento;
      $data->cargo = $result->cargo;

      return $data;
    }

    return null;
  }

}
