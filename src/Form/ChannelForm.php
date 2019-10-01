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

    $form['content_normal'] = array(
      '#type' => 'text_format',
      '#title' => t('Content'),
      '#format'=> $channel->getContentFormat(),
      '#default_value' => $channel->getContent(),
    );

    $form['content_accessibility'] = array(
      '#type' => 'text_format',
      '#title' => t('Content (accessiblity)'),
      '#format'=> $channel->getContentAccessibilityFormat(),
      '#default_value' => $channel->getContentAccessibility(),
    );

    return $form;
  }

  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    drupal_set_message($this->t('Channel created.'));
    $form_state->setRedirectUrl($this->entity->urlInfo('collection'));

    return $status;
  }
}
