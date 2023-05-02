#!/bin/bash
# Date création : 09/02/2016
# description : génération des certificats


clientFolder=finalClient
systemFolder=finalSystem


function checkFile
{
if [ ! -f $1 ] 
then 
	echo -e "[\e[31mERREUR\e[0m] Fichier $1 introuvable : "
	for var in ${2:+"$@"}
	do
		echo -n -e "$var"
	done
	echo ""
fi 
}

#############################################################################################
#initialisation des fonction de generation
source ./preparer.sh
source ./certificatFunctions.sh

#############################################################################################
# Lancement des scripts
preparation $clientFolder $systemFolder > installation_certificat.log 2>&1
signatureSystem signatureSysteme $systemFolder >> installation_certificat.log 2>&1
utilisateurSysteme utilisateurSysteme $systemFolder >> installation_certificat.log 2>&1
horodatageSysteme horodatageSysteme $systemFolder >> installation_certificat.log 2>&1
sslSysteme sslSysteme $systemFolder >> installation_certificat.log 2>&1
certificat administrateurCLIENT $clientFolder  >> installation_certificat.log 2>&1
certificat gestionaireCLIENT $clientFolder  >> installation_certificat.log 2>&1
certificat lecteurCLIENT $clientFolder    >> installation_certificat.log 2>&1
certificat signatureCLIENT $clientFolder  >> installation_certificat.log 2>&1
rm -f $clientFolder/signatureCLIENT.crt

#############################################################################################
# Vérification
errMsgCertificat="Vérifier le fichier de log : installation_certificat.log"
checkFile $clientFolder/administrateurCLIENT.crt $errMsgCertificat
checkFile $clientFolder/administrateurCLIENT.pem $errMsgCertificat
checkFile $clientFolder/administrateurCLIENT.pfx $errMsgCertificat
checkFile $clientFolder/gestionaireCLIENT.crt $errMsgCertificat
checkFile $clientFolder/gestionaireCLIENT.pem $errMsgCertificat
checkFile $clientFolder/gestionaireCLIENT.pfx $errMsgCertificat
checkFile $clientFolder/lecteurCLIENT.crt $errMsgCertificat
checkFile $clientFolder/lecteurCLIENT.pem $errMsgCertificat
checkFile $clientFolder/signatureCLIENT.pem $errMsgCertificat
checkFile $systemFolder/horodatageSysteme.pem $errMsgCertificat
checkFile $systemFolder/signatureSysteme.pem $errMsgCertificat
checkFile $systemFolder/sslSysteme.pem $errMsgCertificat
checkFile $systemFolder/utilisateurSysteme.crt $errMsgCertificat
checkFile $systemFolder/utilisateurSysteme.pem $errMsgCertificat
checkFile $systemFolder/utilisateurSysteme.pfx $errMsgCertificat

#############################################################################################
echo -e "Génération des certificats sur les répertoires clientFolder et systemFolder  :[\e[32mTerminé\e[0m]"

