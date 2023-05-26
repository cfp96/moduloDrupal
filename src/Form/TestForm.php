<?php

namespace Drupal\example_module\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Database\ConnectionNotDefinedException;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure hablame settings for this site.
 */
class TestForm extends FormBase {

  use StringTranslationTrait;

  /**
   * Variable that store the module handler Service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Var that store the logger for the channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;


  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The var for the logger.
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    LoggerChannelFactoryInterface $logger
  ) {
    $this->moduleHandler = $moduleHandler;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'example_module_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $module_path = $this->moduleHandler->getModule('example_module')->getPath();

    $ymlFormFields = Yaml::decode(file_get_contents($module_path . '/assets/yml/form/test.form.yml'));
    foreach ($ymlFormFields as $key => $field) {
      $form[$key] = $field;
    }

    $form['nombre']['#ajax'] = [
      'callback' => '::validarDatosAjaxCallback',
      'event' => 'change',
      'progress' => [
        'type' => 'throbber',
        'message' => $this->t('Validating...'),
      ]
    ];

    $form['identificacion']['#ajax'] = [
      'callback' => '::validarDatosAjaxCallback',
      'event' => 'change',
      'progress' => [
        'type' => 'throbber',
        'message' => $this->t('Validating...'),
      ]
    ];
    $form['#theme'] = 'testform';

    $form['#attached']['library'][] = 'example_module/bootstrap';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormTheme() {
    return [
      'testform' => 'testform.html.twig',
    ];
  }

  /**
   * Ajax callback for the validation in text fields.
   */
  public function validarDatosAjaxCallback(array &$form, FormStateInterface $form_state)
  {
    $response = new AjaxResponse();
    $nameValue = $form_state->getValue('nombre');
    $identificationValue = $form_state->getValue('identificacion');

    $message = '';

    if (!preg_match('/^[a-zA-Z0-9]+$/', $nameValue)) {
      $message = $this->t('El campo debe contener solo caracteres alfanuméricos.');
    } elseif (!preg_match('/^[0-9]*$/', $identificationValue)) {
      $message = $this->t('El campo debe contener solo números.');
    }

    if (!empty($message)) {
      $response->addCommand(new HtmlCommand('#validation-message', $message));
      $response->addCommand(new InvokeCommand('#validation-message', 'removeClass', ['d-none']));
    } else {
      $response->addCommand(new InvokeCommand('#validation-message', 'addClass', ['d-none']));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $name = $form_state->getValue('nombre');
    $identification = $form_state->getValue('identificacion');
    $birthdayDate = $form_state->getValue('fecha_de_nacimiento');
    $jobTitle = $form_state->getValue('cargo');

    if ($jobTitle == 'administrador') {
      $status = 1;
    }
    else {
      $status = 0;
    }

    try {
      $connection = Database::getConnection();
      $query = $connection->insert('example_users')
        ->fields([
          'nombre',
          'identificacion',
          'fecha_de_nacimiento',
          'cargo',
          'estado',
        ])
        ->values([
          $name,
          $identification,
          $birthdayDate,
          $jobTitle,
          $status,
        ])
        ->execute();

      $this->messenger()->addMessage($this->t('Los datos se almacenaron correctamente'));
    }
    catch (ConnectionNotDefinedException | DatabaseExceptionWrapper $e) {
      $this->logger->get('example_module_form')->error('Error al guardar en la base de datos: @error', [
        '@error' => $e->getMessage(),
      ]);
    }
  }

}
