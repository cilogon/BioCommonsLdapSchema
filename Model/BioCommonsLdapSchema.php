<?php

App::uses("JwtAuthenticator", "JwtAuthenticator.Model");

class BioCommonsLdapSchema extends AppModel {

  public $attributes = array(
    'cilogonperson' => array(
      'objectclass' => array(
        'required' => true
      ),
      'attributes' => array(
        'CILogonPersonToken;app-ckan' => array(
          'required' => false,
          'multiple' => true,
        ),
        'CILogonPersonToken;app-galaxy' => array(
          'required' => false,
          'multiple' => true,
        )
      )
    )
  );

  // Required by COmanage Plugins
  public $cmPluginType = "ldapschema";

  // Document foreign keys
  public $cmPluginHasMany = array();

  /**
  * Assemble attributes to write. Required for LDAP schema plugin.
  *
  * @since COmanage Registry 4.2.0
  * @param array $configuredAttributes Array of configured attributes
  * @param array $provisioningData Array of provisioning data
  * @return array Array of attribute names and values to write
  */
  public function assemblePluginAttributes($configuredAttributes, $provisioningData) {

    $attrs = array();

    if(!empty($provisioningData['Jwt'])) {
      $authenticatorModel = new JwtAuthenticator();

      foreach($provisioningData['Jwt'] as $jwt) {
        // Find the JwtAuthenticator and associated Authenticator 
        // and CoService objects.
        $args = array();
        $args['conditions']['JwtAuthenticator.id'] = $jwt['jwt_authenticator_id'];
        $args['contain']['Authenticator'] = 'CoService';

        $authenticatorModel->clear();
        $authenticator = $authenticatorModel->find("first", $args);

        if(!empty($authenticator)) {
          // We assume only a single CoService is linked to the Authenticator.
          if(!empty($authenticator['Authenticator']['CoService'][0]['short_label'])){
            $label = $authenticator['Authenticator']['CoService'][0]['short_label'];
            $attrs["CILogonPersonToken;app-$label"] = $jwt['jwt'];
          }
        }
      }
    }

    return $attrs;
  }

  /**
   * Expose menu items.
   *
   * @since COmanage Registry v0.9.2
   * @return Array with menu location type as key and array of labels, controllers, actions as values.
   */
  public function cmPluginMenus() {
      return array();
  }
}
