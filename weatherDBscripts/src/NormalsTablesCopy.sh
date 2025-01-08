#!/bin/bash

###############################################################################
# Script : copier_tables_normals.sh
# Auteur : [Pierre Stranart]
# Date   : [08/01/2025]
# Objectif :
# Ce script copie toutes les tables d'une base de données source dont le nom 
# commence par "Normals_*" vers une base de données cible. Lors de la copie, 
# les tables sont renommées en ajoutant un préfixe configurable devant 
# leurs noms d'origine. Le script utilise `mysqldump` pour exporter les tables 
# et `mysql` pour les réimporter avec leurs nouveaux noms.
#
# Fonctionnement :
# 1. Se connecte à la base de données source et récupère la liste des tables 
#    correspondant au pattern "Normals_*".
# 2. Pour chaque table trouvée :
#    a) Elle est exportée dans un fichier temporaire au format SQL.
#    b) Le fichier SQL est modifié pour inclure le nouveau nom de table 
#       (avec le préfixe configuré).
#    c) Le fichier modifié est importé dans la base de données cible.
# 3. Nettoie les fichiers temporaires après chaque import.
#
# Pré-requis :
# - L'utilisateur MySQL doit avoir les droits nécessaires sur les bases source 
#   et cible (SELECT pour la source, INSERT et CREATE pour la cible).
# - Les commandes `mysql`, `mysqldump` et `sed` doivent être installées sur 
#   le système.
#
# Utilisation :
# ./copier_tables_normals.sh
#
# Warning: 
# Besoin spécifique d'administration, usage éphémère !
###############################################################################

# Configuration des bases de données
SRC_DB="BethuneWeatherReport"   # Base de données source
DEST_DB="ClimateNormals"        # Base de données cible
SRC_USER="xxxxx"                # Nom d'utilisateur MySQL
SRC_PASS="xxxxxx"               # Mot de passe MySQL à mettre à jour avant chaque execution
HOST="192.168.17.10"            # Hôte MySQL

# Préfixe pour renommer les tables
TABLE_PREFIX="LilleLesquin_" # Préfixe à ajouter aux noms des tables destination

# Commandes MySQL
MYSQL_CMD="mysql -u$SRC_USER -p$SRC_PASS -h$HOST"
MYSQLDUMP_CMD="mysqldump -u$SRC_USER -p$SRC_PASS -h$HOST"

# Récupérer la liste des tables correspondant au pattern "Normals_*"
TABLES=$($MYSQL_CMD -N -e "SHOW TABLES LIKE 'Normals_%'" $SRC_DB)

if [[ -z "$TABLES" ]]; then
  echo "Aucune table correspondante trouvée dans la base de données source."
  exit 1
fi

# Exporter et importer les tables
for TABLE in $TABLES; do
  NEW_TABLE="${TABLE_PREFIX}${TABLE}"
  
  echo "Copie de la table $TABLE vers $NEW_TABLE..."
  
  # Exportation de la table
  $MYSQLDUMP_CMD $SRC_DB $TABLE > /tmp/$TABLE.sql
  
  # Modifier le nom de la table dans le fichier SQL
  sed -i "s/\`$TABLE\`/\`$NEW_TABLE\`/g" /tmp/$TABLE.sql
  
  # Importer dans la base de données cible
  $MYSQL_CMD $DEST_DB < /tmp/$TABLE.sql
  
  # Nettoyage du fichier temporaire
  rm -f /tmp/$TABLE.sql
done

echo "Toutes les tables ont été copiées et renommées avec succès."
