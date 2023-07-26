<?php

namespace Drupal\kififeedback\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\kififeedback\ChannelInterface;

/**
 * Feedback channel (source website).
 *
 * @ConfigEntityType(
 *   id = "kififeedback_channel",
 *   label = @Translation("Feedback channel"),
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\kififeedback\Form\ChannelForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "list_builder" = "Drupal\kififeedback\ChannelListBuilder",
 *   },
 *   admin_permission = "administer content types",
 *   config_prefix = "channel",
 *   bundle_of = "kififeedback",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name"
 *   },
 *   links = {
 *     "edit-form" = "/admin/structure/channels/{kififeedback_channel}",
 *     "delete-form" = "/admin/structure/channels/{kififeedback_channel}/delete",
 *     "collection" = "/admin/structure/channels",
 *   },
 *   config_export = {
 *     "id",
 *     "name",
 *     "url",
 *     "status",
 *     "description_normal",
 *     "description_accessibility",
 *   }
 * )
 */
class Channel extends ConfigEntityBundleBase implements ChannelInterface {
  public const STATUS_DISABLED = 0;
  public const STATUS_ENABLED = 1;

  protected $id;
  protected $name;
  protected $url;
  protected $status;
  protected $description_normal;
  protected $description_accessibility;

  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    
    // Set reasonable defaults for ckeditor enabled fields.
    if(!$this->description_normal) {
      $this->description_normal = ['value' => '', 'format' => 'basic_html'];
    }
    if(!$this->description_accessibility) {
      $this->description_accessibility = ['value' => '', 'format' => 'basic_html'];
    }
  }

  public function getName() {
    return $this->name;
  }

  public function setName($name) {
    $this->name = $name;
  }

  public function getUrl() {
    return $this->url;
  }

  public function setUrl($url) {
    $this->url = $url;
  }

  public function setStatus($status) {
    $this->status = (int)$status;
  }

  public function getStatus() {
    return $this->status;
  }

  public function isEnabled() {
    return $this->getStatus() == self::STATUS_ENABLED;
  }

  public function getDescription() {
    return $this->description_normal['value'];
  }

  public function getDescriptionFormat() {
    return $this->description_normal['format'];
  }

  public function setDescription($content) {
    $this->description_normal['value'] = $content;
  }

  public function setDescriptionFormat($format) {
    $this->description_normal['format'] = $format;
  }

  public function getDescriptionAccessibility() {
    return $this->description_accessibility['value'];
  }

  public function setAccessibilityDescription($content) {
    $this->description_accessibility['value'] = $content;
  }
  
  public function getDescriptionAccessibilityFormat() {
    return $this->description_accessibility['format'];
  }

  public function setDescriptionAccessibilityFormat($format) {
    $this->description_accessibility['format'] = $format;
  }

}
