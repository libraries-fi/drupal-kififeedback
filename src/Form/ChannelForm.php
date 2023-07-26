<?php

namespace Drupal\kififeedback\Form;

use Drupal\Core\Entity\BundleEntityFormBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;

class ChannelForm extends BundleEntityFormBase {
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $channel = $this->entity;

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $channel->getName(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => [
        'exists' => '\Drupal\kififeedback\Entity\Channel::load',
        'source' => ['name'],
      ],
      '#disabled' => !$channel->isNew(),
      '#default_value' => $channel->id(),
    ];

    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#default_value' => $channel->getUrl(),
      '#required' => FALSE,
    ];

    $form['description_normal'] = ['#type' => 'text_format', '#title' => t('Description'), '#format'=> $channel->getDescriptionFormat(), '#default_value' => $channel->getDescription()];

    $form['description_accessibility'] = ['#type' => 'text_format', '#title' => t('Description (accessiblity)'), '#format'=> $channel->getDescriptionAccessibilityFormat(), '#default_value' => $channel->getDescriptionAccessibility()];

    return $form;
  }

  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $this->messenger()->addStatus($this->t('Channel created.'));
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));

    return $status;
  }
}
