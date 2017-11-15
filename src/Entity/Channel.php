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
 *   }
 * )
 */
class Channel extends ConfigEntityBundleBase implements ChannelInterface {
  const STATUS_DISABLED = 0;
  const STATUS_ENABLED = 1;

  protected $id;
  protected $name;
  protected $url;
  protected $status;

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
}
