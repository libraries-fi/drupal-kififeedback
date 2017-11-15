<?php

namespace Drupal\kififeedback\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class FeedbackFilterForm extends FormBase {
  public function getFormID() {
    return 'kififeedback_filter_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $request = $this->getRequest();

    $form['#method'] = 'get';
    $form['#attributes']['class'] = ['views-exposed-form', 'form--inline'];
    $form['#attached']['library'][] = 'views/views.exposed-form';

    $form['channel'] = [
      '#type' => 'select',
      '#title' => $this->t('Channel'),
      '#options' => ['' => $this->t('- Any -')] + $this->getChannelOptions(),
      '#default_value' => $request->query->get('channel')
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#attributes' => ['name' => ''],
        '#value' => $this->t('Filter'),
      ]
    ];

    return $form;
  }

  private function getChannelOptions() {
    $channels = \Drupal::entityTypeManager()->getStorage('kififeedback_channel')->loadMultiple();
    $options = [];

    foreach ($channels as $entity) {
      $options[$entity->id()] = $entity->label();
    }

    asort($options);

    return $options;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

  }
}
