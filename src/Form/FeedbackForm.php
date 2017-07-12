<?php

namespace Drupal\kififeedback\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;

class FeedbackForm extends ContentEntityForm {
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $feedback = $this->entity;
    $form['comment']['#access'] = FALSE;

    $form['name']['widget'][0]['value']['#title'] = $this->t('Your name');
    $form['email']['widget'][0]['value']['#title'] = $this->t('Your email address');

    if ($this->currentUser()->isAuthenticated()) {
      $user = $this->currentUser();

      $form['name']['widget'][0]['value']['#type'] = 'item';
      $form['name']['widget'][0]['value']['#plain_text'] = $user->getDisplayName();
      $form['name']['widget'][0]['value']['#value'] = $user->getDisplayName();

      $form['email']['widget'][0]['value']['#type'] = 'item';
      $form['email']['widget'][0]['value']['#plain_text'] = $user->getEmail();
      $form['email']['widget'][0]['value']['#value'] = $user->getEmail();
    }

    return $form;
  }

  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Submit');
    return $actions;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    drupal_set_message($this->t('Thank you for your feedback.'));
  }
}
