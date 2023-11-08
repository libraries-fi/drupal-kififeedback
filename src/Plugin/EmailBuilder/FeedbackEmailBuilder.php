<?php
namespace Drupal\kififeedback\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailFactoryInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Processor\EmailBuilderBase;
use Drupal\user\UserInterface;
use Drupal\symfony_mailer\Address;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\kififeedback\Entity\Feedback;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;

/**
 * Defines the Email Builder plug-in for user module.
 *
 * @EmailBuilder(
 *   id = "kififeedback",
 *   label = @Translation("Kirjastot.fi feedback"),
 *   sub_types = {
 *     "reply" = @Translation("Reply"),
 *     "forward" = @Translation("Forward"),
 *   },
 *   override = {"kififeedback.reply", "kififeedback.forward"}
 * )
 */
class FeedbackEmailBuilder extends EmailBuilderBase {

  use TokenProcessorTrait;

  public function createParams(EmailInterface $email, AccountInterface|string $to = NULL, $langcode = "fi", Feedback $feedback = NULL, $reply_to = "") {
    assert($feedback != NULL);
    assert($to != NULL);
    $email->setParam('to', $to);
    $email->setParam('langcode', $langcode);
    $email->setParam('feedback', $feedback);
    $email->setVariable('feedback', $feedback);
    $email->setParam('kififeedback', $feedback);
    $email->setVariable('kififeedback', $feedback);
    $email->setParam('reply_to', $reply_to);
  }

  public function fromArray(EmailFactoryInterface $factory, array $message) {
    
    $reply_to = '';
    if($message['key'] == 'forward')
    {
      if(!empty($message['reply-to'])) {
        $reply_to = $message['reply-to'];
      } else if(!empty($message['params']['from']))
      {
        $reply_to = $message['params']['from']->getEmail();
      }
    }

    return $factory->newTypedEmail($message['module'], $message['key'], $message['toy'],
      $message['langcode'], $message['params']['kififeedback'], $reply_to);
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $this->tokenOptions(['clear' => TRUE]);
  }

  public function build(EmailInterface $email) {

    $feedback = $email->getParam('feedback');

    $variables = [];
    $variables['entity'] = $feedback;
    $variables['subject'] = $feedback->getSubject();
    $variables['author'] = $feedback->getName();
    $variables['langcode'] = $email->getParam('langcode');
    $variables['with_signature'] = true;

    $user = \Drupal::entityTypeManager()->getStorage('user')->load(\Drupal::currentUser()->id());
    $variables['signature'] = $user->get('field_email_signature')->value;

    if(empty($variables['signature']))
    {
      $variables['with_signature'] = false;
      unset($variables['signature']);
    }

    $email->setVariables($variables);

    if($email->getSubType() == 'forward')
    {
      $reply_to = $email->getParam('reply_to');
      $email->setReplyTo(new Address($reply_to));

      $to = $email->getParam('to');
      $address = NULL;

      if(is_string($to))
      {
        $address = new Address($to);
      } else if($to instanceof AccountInterface)
      {
        $address = new Address($to->getEmail(), $to->getDisplayName());
      }
    
      $email->setTo($address);
    } 
    else {
      // Reply
      $address = new Address($feedback->getEmail(), $feedback->getName(), NULL);
    }

  }

}