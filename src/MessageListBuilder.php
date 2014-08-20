<?php

/**
 * @file
 * Contains \Drupal\contact_storage\MessageListBuilder.
 */

namespace Drupal\contact_storage;

use Drupal\Component\Utility\String;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list builder for contact message entity.
 */
class MessageListBuilder extends EntityListBuilder {

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $queryFactory;

  /**
   * Constructs a new MessageListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_info
   *   The entity info for the entity type.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage controller class.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke hooks on.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   The entity query factory.
   */
  public function __construct(EntityTypeInterface $entity_info, EntityStorageInterface $storage, ModuleHandlerInterface $module_handler, QueryFactory $query_factory) {
    parent::__construct($entity_info, $storage, $module_handler);
    $this->queryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_info) {
    return new static(
      $entity_info,
      $container->get('entity.manager')->getStorage($entity_info->id()),
      $container->get('module_handler'),
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $result = $this->queryFactory->get($this->entityTypeId)
      ->pager(20)
      ->execute();
    return $this->storage->loadMultiple($result);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = array(
      'subject' => $this->t('Subject'),
      'contact_form' => $this->t('Contact form'),
    );
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['subject'] = String::checkPlain($entity->getSubject());
    $row['contact_form'] = String::checkPlain($entity->getContactForm()->label());
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['list'] = parent::render();
    $build['pager']['#theme'] = 'pager';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    // @todo make that unit testable.
    $destination = drupal_get_destination();
    $operations['edit']['query'] = $destination;
    $operations['delete']['query'] = $destination;
    return $operations;
  }

}
