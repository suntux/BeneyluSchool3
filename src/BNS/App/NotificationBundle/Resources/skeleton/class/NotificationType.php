<?php

namespace BNS\App\NotificationBundle\Notification\{{ bundleName }}Bundle;

use BNS\App\NotificationBundle\Model\NotificationInterface;
use BNS\App\NotificationBundle\Model\Notification;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Notification generation date : {{ date }}
 */
class {{ className }}Notification extends Notification implements NotificationInterface
{
    const NOTIFICATION_TYPE = '{{ type }}';

    /**
     * @param ContainerInterface $container Services container
     {% for attribute in attributes %}
* @param type ${{ attribute }}
     {% endfor %}* @param int $groupId L'ID du groupe de l'utilisateur qui va recevoir la notification
     */
    public function __construct(ContainerInterface $container, {{ phpAttributes }}$groupId = null)
    {
        parent::__construct();
        $this->init($container, $groupId, self::NOTIFICATION_TYPE, array(
        {% for attribute in attributes %}
    '{{ attribute }}' => ${{ attribute }},
        {% endfor %}));
    }
    
    /**
     * @param Notification $notification
     * @param array $objects Les paramètres de la notifications
     *
     * @return array Les traductions de la notification
     */
    public static function translate(Notification $notification, $objects)
    {
        $finalObjects = array();

        // Faites les modifications nécessaires à la restitution des paramètres ci-dessous
        // Le container est accessible grâce à l'attribut statique "self::$container"
        {% for attribute in attributes %}
$finalObjects['%{{ attribute }}%'] = $objects['{{ attribute }}'];
        {% endfor %}

        /*
         * Vous pouvez aussi créer un tableau de traduction selon vos propres règles de nommage grâce
         * à cette méthode : parent::getTranslation($notification, $finalObjects, self::NOTIFICATION_TYPE . '.REGLE DE NOMMAGE')
         * Exemple : $results['NEW_ENGINE'] = parent::getTranslation($notification, $finalObjects, self::NOTIFICATION_TYPE . '.NEW_ENGINE.content'); return $results;
         */

        return parent::getTranslation($notification, $finalObjects);
    }
}
