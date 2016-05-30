<?php

namespace Fidesio\IsidoreBundle\Component\TableBD;

class Error
{
    const ERR_INCONNUE                  = 0;        // Erreur inconnue
    const ERR_CLEFS_JOINTS_ENREG_INCOH  = 1;		// La clef externe et la clef primaire d'un jointure n'ont pas la même valeur dans l'enregistrement
    const ERR_CLEF_PRIM_ABSENTE 		= 2;		// L'enregistrement n'a pas de clef primaire
    const ERR_ENREG_POINTE_OBLIG 		= 3;		// L'enregitrement doit obligatoirement pointer vers un enregistrement de la table pointée
    const ERR_NOUV_ENREG_EXISTE 		= 4;		// La clef primaire du nouvel enregistrement existe déjà en base
    const ERR_CHOIX_POINTE_INTERDIT 	= 5;		// Le choix de la table pointée est interdit à partir de cette table
    const ERR_MAJ_POINTE_INTERDITE 		= 6;		// La mise à jour de la table pointé est interdite à partir de cette table
    const ERR_CREA_POINTE_INTERDITE  	= 7;		// La création d'un enregistrement pointé est interdite à partir de cette table
    const ERR_CLEFS_ENREG_BASE_INCOH 	= 8;		// Les valeur d'une clef de jointure sont différentes dans l'enregistrement envoyé et dans la base
    const ERR_DUPLI_INDEX_UNIQUE 		= 9;		// Duplication d'une index unique
    const ERR_PAS_ENREG_DE_SSTABLE		= 10;		// Cet enregistrement existe dans la table principale, mais n'appartient pas à la sous table
    const ERR_ENREG_ABSENT				= 11;		// L'enregistrement n'existe pas dans la table
    const ERR_POINTEUR_MULTIPLE			= 12;		// Un enregistrement pointeur pointe déjà vers cet enregistrement pointé (cas de pointeur unique)
    const ERR_ENREG_POINTEUR_OBLIG		= 13;		// Pointeur obligatoire
    // Vérification format SQL
    const ERR_PARAM_SQL_MANQUANT		= 20;		// Le paramètre complétant le type SQL (nombre de cahractères, de décimales, etc.) est manquant
    const ERR_NULL_PAS_ACCEPTE			= 21;		// La valeur NULL n'est pas accepté pour ce champ
    const ERR_VAL_NUM_HORS_LIMITES		= 22;		// La valeur est en dehors des limites définies pour ce type de variable numérique
    const ERR_VAL_NON_ENTIERE			= 23;		// La valeur numérique n'est pas entière
    const ERR_VALEUR_NON_NUMERIQUE		= 24;		// La valeur n'est pas une valeur numérique
    const ERR_TEXTE_TROP_LONG			= 25;		// Le texte est trop long;
    const ERR_VAL_HORS_LISTE			= 26;		// La valeur ne fait pas partie de la liste des valeurs acceptables.
    const ERR_CHAMP_INCONNU_EN_BASE		= 40;		// Champ inconnu dans la base
    const ERR_INDEX_UNIQUE_ABSENT		= 41;		// Un index unique est absent d'un enregistrement à modifier
    const ERR_HISTO_SANS_CLEF_PRIM		= 42;		// L'historique automatique ne peut fonctionner qu'avec une table munie d'une clef primaire
    const ERR_PLUSIEURS_ENREG_CORRES	= 43;		// Plusieurs enregistrement correspondent à la clause WHERE, alors qu'on en attend qu'un seul
    const ERR_CHAMP_PAS_INDEX_UNIQUE	= 44;		// Ce champs devrait être déclaré dans sa table comme index unique
    const ERR_PERM_CHAINE_NON_CONF		= 50;		// La chaîne de permission est non conforme
    const ERR_MAUVAIS_NBR_ARGS			= 51;       // Mauvais nombre d'arguments
    const ERR_ARGUMENTS_INCOHERENTS		= 52;  		// Les arguments passés à la fonction sont incohérents
    const ERR_ACTION_INTERDITE			= 53;		// Action interdite
    const ERR_MAUVAIS_TYPE_OBJET		= 54;		// L'objet passé en argument n'a pas le bon type
    const ERR_ACTION_NON_AUTORISEE      = 55;		// Cette action n'est pas autorisée pour cet utilisateur
    const ERR_TABLE_INEXISTANTE			= 56;		// Table utilisateur inexistante
    const ERR_ACCES_INEXISTANT			= 57;		// Il n'existe aucun accès correspondant à cet id_visiteur
    const ERR_MODULE_INEXISTANT			= 58;		// Le module demandé n'existe pas
    // Alertes
    const AL_POINTE_PAS_ENREGISTRE		= 100;		// Les éventuelles modifications de l'enregistrement pointé n'ont pas été enregistrées
    const AL_ENREG_ABSENT				= 101;		// L'enregistrement n'existe pas dans la table
    const AL_CHAMP_INCONNU_EN_BASE		= 102;		// Le champ est inconnu dans la table de la base
    const ERR_DROITS_INSUFFISANTS  		= 103;      // Le visiteur n'a pas les droits pour cette opération
    const ERR_INFO_CONNEX_ABSENTE		= 200;
    const ERR_EXECUTION_SQL        		= 201;
    const ERR_FICHIER_INTROUVABLE  		= 202;
    const INVALID_CREDENTIAL            = 403;
    const ERR_MAJ_POINTE_VERROU         = 600;      // Modification interdite sur un enrg pointé
    const ERR_SUP_POINTE_VERROU         = 601;      // Suppression interdite sur un enrg pointé
}
