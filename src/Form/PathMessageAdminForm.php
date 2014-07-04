<?php

/**
 * @file
 * Contains Drupal\path_message\Form\PathMessageAdminForm
 */

namespace Drupal\path_message\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\String;

/**
 * Provides and admin form for Path Message.
 */
class PathMessageAdminForm extends ConfigFormBase {

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $conditionManager;

  /**
   * The request path condition.
   *
   * @var \Drupal\system\Plugin\Condition\RequestPath $condition
   */
  protected $condition;

  /**
   * Creates a new PathMessageAdminForm.
   *
   * @param ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   *   The condition manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ExecutableManagerInterface $condition_manager) {
    parent::__construct($config_factory);
    $this->conditionManager = $condition_manager;
    $this->condition = $this->conditionManager->createInstance('request_path');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.condition')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'path_message_admin';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    // Load our default configuration.
    $config = $this->config('path_message.settings');

    // Set the default condition configuration.
    $this->condition->setConfiguration($config->get('request_path'));

    $form['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Enter the message you want to appear'),
      '#default_value' => $config->get('message'),
    );

    // Build the configuration form.
    $form += $this->condition->buildConfigurationForm($form, $form_state);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {

    $this->condition->submitConfigurationForm($form, $form_state);
    $this->config('path_message.settings')
      ->set('message', String::checkPlain($form_state['values']['message']))
      ->set('request_path', $this->condition->getConfiguration())
      ->save();

    parent::submitForm($form, $form_state);
  }

}
