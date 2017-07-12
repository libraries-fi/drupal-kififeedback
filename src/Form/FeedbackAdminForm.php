<?php

namespace Drupal\kififeedback\Form;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\kififeedback\LogEntryInterface;

class FeedbackAdminForm extends FeedbackAdminFormBase {
  public function form(array $form, FormStateInterface $form_state) {
    $feedback = $this->entity;
    $form = parent::form($form, $form_state);

    $form['message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Response'),
      '#format' => 'basic_html_without_ckeditor',
      '#rows' => 10,
    ];

    return $form;
  }

  public function sendMessage(array $form, FormStateInterface $form_state) {
    $feedback = $this->entity;
    $message = $form_state->getValue('message');
    $langcode = $feedback->language()->getId();

    $log_entry = $this->entityManager->getStorage('kififeedback_log')->create([
      'action' => LogEntryInterface::ACTION_RESPOND,
      'message' => $message,
    ]);

    $feedback->addActionToLog($log_entry);

    $this->mailer->mail('kififeedback', 'reply', $feedback->getEmail(), $langcode, [
      'from' => $this->currentUser(),
      'kififeedback' => $feedback,
    ]);

    drupal_set_message($this->t('Response to feedback was submitted.'));
  }

  public function saveSuccessMessage(array $form, FormStateInterface $form_state) {
    drupal_set_message($this->t('The changes have been saved.'));
  }

  public function validateHasEmail(array $form, FormStateInterface $form_state) {
    if (!$this->entity->getEmail()) {
      $form_state->setError($form['feedback_info']['user_email'], $this->t('Cannot respond without an email address.'));
    }
  }

  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#dropbutton'] = 'save';

    $actions['send'] = $actions['submit'];
    $actions['send']['#value'] = $this->t('Send message');
    $actions['send']['#validate'] = ['::validateForm', '::validateHasEmail'];

    $actions['submit']['#submit'][] = '::saveSuccessMessage';

    $pos = array_search('::save', $actions['send']['#submit']);
    array_splice($actions['send']['#submit'], $pos, 0, '::sendMessage');

    return $actions;
  }
}
