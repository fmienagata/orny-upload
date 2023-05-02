function preparation # tmpClient tmpSysteme
{
[[ -d $1 ]] && rm -rf $1
[[ -d $2 ]] && rm -rf $2
mkdir $1
mkdir $2

echo "-----------------Creation du CA"
[[ -d ./escCA ]] && rm -rf ./escCA
mkdir ./escCA
mkdir ./escCA/certs
mkdir ./escCA/private
mkdir ./escCA/newcerts

echo 1000 > ./escCA/serial
touch ./escCA/index.txt

chmod -R 700 ./escCA

openssl req -new -x509 -days 3650 -extensions v3_ca -keyout ./escCA/private/cakey.pem -out ./escCA/cacert.pem -config ./ca.cnf -passout pass:esc1234 -subj /C=FR/ST=IDF/L=Paris/O=Docapost-dps/OU=ESC/CN=ESC-CA

openssl x509 -text -in ./escCA/cacert.pem
}