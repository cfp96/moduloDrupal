<?php

namespace Drupal\crud_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controlador para realizar operaciones CRUD en una tabla.
 */
class DataController extends ControllerBase {

  protected $database;

  protected $renderer;

  /**
   * Constructor del controlador.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   La conexión a la base de datos.
   */
  public function __construct(
    Connection $database,
    RendererInterface $renderer
    ) {
    $this->database = $database;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('renderer')
    );
  }

  /**
   * Show the user data.
   */
  public function showData() {

    $header_table = [
      'id' => $this->t('ID'),
      'nombre' => $this->t('Nombre'),
      'identificacion' => $this->t('Identificacion'),
      'fecha_de_nacimiento' => $this->t('Fecha de nacimiento'),
      'cargo' => $this->t('Cargo'),
      'edit' => $this->t('Editar'),
      'delete' => $this->t('Eliminar'),
    ];


    $query = $this->database->select('example_users', 'eu');
    $query->fields('eu', [
      'id',
      'nombre',
      'identificacion',
      'fecha_de_nacimiento',
      'cargo'
    ]);
    $results = $query->execute()->fetchAll();

    $rows = [];
    foreach ($results as $result) {

      $delete = Url::fromUserInput('/example-crud/delete/' . $result->id);
      $edit = Url::fromUserInput('/example-crud/edit/' . $result->id);

      $rows[] = [
        'id' => $result->id,
        'nombre' => $result->nombre,
        'identificacion' => $result->identificacion,
        'fecha_de_nacimiento' => $result->fecha_de_nacimiento,
        'cargo' => $result->cargo,
        'edit' => Link::fromTextAndUrl('Editar', $edit)->toString(),
        'delete' => Link::fromTextAndUrl('Eliminar', $delete)->toString(),
      ];
    }

    $add = Url::fromUserInput('/example-module/form');
    $text = "Agregar Usuario";

    $data['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => $this->t('No se encontraron registros'),
      '#caption' => Link::fromTextAndUrl($text, $add)->toString(),
    ];

    return $data;
  }

}
