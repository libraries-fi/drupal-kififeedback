<?php

namespace Drupal\kififeedback\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\kififeedback\LogEntryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FeedbackAdminFormBase extends ContentEntityForm {
  protected $dateFormatter;
  protected $mailer;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('date.formatter'),
      $container->get('plugin.manager.mail')
    );
  }

  public function __construct(EntityManagerInterface $entity_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL, DateFormatter $date_formatter, MailManagerInterface $mailer) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->dateFormatter = $date_formatter;
    $this->mailer = $mailer;
  }

  public function form(array $form, FormStateInterface $form_state) {
    $feedback = $this->entity;
    $form['#attached']['library'][] = 'node/form';

    $form['advanced'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['entity-meta']],
      '#weight' => 99,
    ];

    $form = parent::form($form, $form_state);
    $form['subject']['#access'] = FALSE;
    $form['body']['#access'] = FALSE;

    $form['body_wrapper'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#weight' => -100,
      '#title' => $feedback->getSubject() ?: $this->t('Feedback'),
      'message' => [
        '#type' => 'processed_text',
        '#format' => $feedback->getBodyFormat(),
        '#text' => $feedback->getBody(),
      ]
    ];

    $form['advanced_header'] = [
      '#type' => 'container',
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['entity-meta__header'],
      ]
    ];

    $form['feedback_info'] = [
      '#type' => 'container',
      '#group' => 'advanced_header',

      'time' => [
        '#type' => 'item',
        '#title' => $this->t('Received on'),
        '#plain_text' => $this->dateFormatter->format($feedback->getCreatedTime())
      ]
    ];

    $form['user_info'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#title' => $this->t('Author'),
      '#open' => TRUE,

      'name' => $form['name'],
      'email' => $form['email'],
    ];

    unset($form['name'], $form['email']);

    $form['log'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#open' => TRUE,
      '#title' => $this->t('Log'),
    ];

    foreach ($feedback->getActions() as $delta => $item) {
      $entry = $item->entity;

      $form['log'][$delta] = [
        '#type' => 'container',
        '#weight' => -1 * $delta,

        'action' => [
          '#plain_text' => $entry->label(),
          '#suffix' => ' ',
        ],
        'user' => [
          '#type' => 'link',
          '#url' => $entry->getUser()->urlInfo(),
          '#title' => $entry->getUser()->label(),
        ]
      ];

      $form['log'][$delta] = [
        '#weight' => -1 * $delta,
        '#theme' => 'kififeedback_log',
        '#entry' => $entry,
      ];
    }

    if (!isset($form['log'][0])) {
      $form['log']['no_results'] = [
        '#type' => 'item',
        '#plain_text' => $this->t('No events.')
      ];
    }

    return $form;
  }
}
