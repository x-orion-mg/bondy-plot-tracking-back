<?php

namespace App\DataFixtures;

use App\Entity\ErrorType;
use App\Entity\EventType;
use App\Entity\ResourceType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SiemFixtures extends Fixture implements FixtureGroupInterface,OrderedFixtureInterface
{
	public function getOrder(): int {
		return 10; // Ordre d'exécution
	}
	public static function getGroups(): array {
		return [ 'siem' ];
	}
	public function load(ObjectManager $manager): void {
		$this->loadEventType($manager);
		$this->loadErrorType($manager);
		$this->loadResourceType($manager);
	}
	private function loadEventType(ObjectManager $manager): void {
		$eventTypes = [
			['LOGIN_SUCCESS', 'Connexion réussie'],
			['LOGIN_FAILURE', 'Connexion échouée'],
			['RESOURCE_ACCESS', 'Accès ressource'],
			['EMAIL_SENT', 'Email envoyé'],
			['EMAIL_FAILURE', 'Echec Email'],
			['PASSWORD_CHANGE', 'Changement de mot de passe'],
			['PASSWORD_RESET', 'Réinitialisation de mot de passe'],
			['USER_CREATION', 'Création d’utilisateur'],
			['USER_DELETION', 'Suppression d’utilisateur'],
			['USER_UPDATE', 'Mise à jour d’utilisateur'],
			['USER_LOGIN', 'Connexion utilisateur'],
			['USER_LOGOUT', 'Déconnexion utilisateur'],
			['DATA_EXPORT', 'Export de données'],
			['DATA_IMPORT', 'Import de données'],
			['DATA_IMPORT', 'Import de données'],
			['ENTITY_CREATE', 'Ajout d’un élément'],
			['ENTITY_UPDATE', 'Modification d’un élément'],
			['ENTITY_DELETE', 'Édition d’un élément'],
		];

		foreach ($eventTypes as [$code, $label]) {
			$e = new EventType();
			$e->setCode($code);
			$e->setLabel($label);
			$manager->persist($e);
		}
	}

	private function loadErrorType(ObjectManager $manager): void {
		$errorTypes = [
			['NONE', 'Pas d’erreur'],
			['INVALID_CREDENTIALS', 'Identifiants invalides'],
			['ACCESS_DENIED', 'Accès refusé'],
			['SYSTEM_ERROR', 'Erreur système'],
			['EMAIL_EXCEPTION', 'Erreur d\'email']
		];

		foreach ($errorTypes as [$code, $label]) {
			$e = new ErrorType();
			$e->setCode($code);
			$e->setLabel($label);
			$manager->persist($e);
		}
	}

	private function loadResourceType(ObjectManager $manager): void {
		$resourceTypes = [
			['FILE', 'Fichier'],
			['DATABASE', 'Base de données'],
			['API', 'API'],
			['EMAIL', 'Email'],
			['SYSTEM', 'Système'],
			['USER', 'Utilisateur'],
			['APPLICATION', 'Application'],
			['NETWORK', 'Réseau'],
			['SERVER', 'Serveur'],
			['DEVICE', 'Appareil'],
			['SERVICE', 'Service'],
			['ALLOCATED_TREE', 'Arbre alloué'],
			['CATEGORY_TREE_SPECIES', 'Arbre catégorie espèce'],
			['CERTIFICATE', 'Cértificat'],
			['CITY', 'Ville'],
			['COUNTRY', 'Pays'],
			['CUSTOMER_IMPORT', 'Customer import'],
			['CUSTOMER_IMPORT_USER', 'Utilisateur import client'],
			['GALLERY', 'Gallery'],
			['IMPACT', 'Impact'],
			['IMPACT_CATEGORY', 'Catégorie d\'impact'],
			['IMPACT_PROJECT', 'Impact project'],
			['LOCALISATION', 'Localisation'],
			['PARCEL', 'Parcel'],
			['PARCEL_TREE_SPECIES', 'Espèce arbre dans les parcelles'],
			['PARTNER_CUSTOMER', 'Client des partenaires'],
			['PROFILE', 'ProfiLe'],
			['PROFILE_ROLE', 'Rôle du profil'],
			['PROJECT_CHILD', 'Projet enfant - Projet Partenaire'],
			['PROJECT_PARENT', 'Projet parent - Projet BONDY'],
			['PROJECT_STATUS', 'Statut du projet'],
			['PROJECT_STEP', 'Etape du projet'],
			['PROJECT_TYPE', 'type de projet'],
			['REGION', 'Region'],
			['REPORT_HISTORY', 'historique rapport'],
			['ROLE', 'Role'],
			['STORY_TELLING', 'Story telling'],
			['SUB_PROJECT', 'Sous projet'],
			['TREE_SPECIES', 'Espèces d\'arbres'],
			['USAGE_TYPE', 'Type d\'utilisation'],
			['USER_OPTION', 'option utilisateur'],
		];

		foreach ($resourceTypes as [$code, $label]) {
			$r = new ResourceType();
			$r->setCode($code);
			$r->setLabel($label);
			$manager->persist($r);
		}

		$manager->flush();
	}
}
