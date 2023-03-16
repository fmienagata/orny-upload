#!/usr/bin/env bash

while [[ $# -gt 0 ]]
do
    case "$1" in
        -v|--version)
            version=$2
            shift # pass value
        ;;
        -n|--minor)
            UPLOAD_VERSION_MINOR=$2
            shift # pass value
        ;;
        -p|--patch)
            UPLOAD_VERSION_PATCH=$2
            shift # pass value
        ;;
        -b|--build)
            UPLOAD_VERSION_BUILD=$2
            shift # pass value
        ;;
        *)
            echo "The \"$1\" option does not exist."
            usage
            exit 1
        ;;
    esac
    shift # pass argument or value
done

IFS=. read UPLOAD_VERSION_MAJOR UPLOAD_VERSION_MINOR UPLOAD_VERSION_PATCH UPLOAD_VERSION_BUILD <<< "${BUILD_DISPLAY_NAME##*-}"

#Check if WORKSPACE exists
if [ -z "${WORKSPACE}" ];
then
    WORKSPACE="."
else
    echo "${WORKSPACE}"
fi

if [ -z "$UPLOAD_VERSION_MAJOR" ];
then
    UPLOAD_VERSION_MAJOR=1
else
    echo "${UPLOAD_VERSION_MAJOR}"
fi

if [ -z "$UPLOAD_VERSION_MINOR" ];
then
    UPLOAD_VERSION_MINOR=0
else
    echo "${UPLOAD_VERSION_MINOR}"
fi

if [ -z "$UPLOAD_VERSION_PATCH" ];
then
    UPLOAD_VERSION_PATCH=0
else
    echo "${UPLOAD_VERSION_PATCH}"
fi

#Set variables
i=1

#Read in template one line at the time, and replace variables (more
#natural (and efficient) way, thanks to Jonathan Leffler).
while IFS= read -r line
do
    sed -i "s/VERSION_MAJOR/${UPLOAD_VERSION_MAJOR}/g" "${WORKSPACE}/CHANGELOG.md"
    sed -i "s/VERSION_MINOR/${UPLOAD_VERSION_MINOR}/g" "${WORKSPACE}/CHANGELOG.md"
    sed -i "s/VERSION_PATCH/${UPLOAD_VERSION_PATCH}/g" "${WORKSPACE}/CHANGELOG.md"
    sed -i "s/VERSION_BUILD/${UPLOAD_VERSION_BUILD}/g" "${WORKSPACE}/CHANGELOG.md"
    ((i++))
    if [[ $i -eq 10 ]]; then
        break
    fi
done < "${WORKSPACE}/CHANGELOG.md"

#Set variables
l=1
# checking the changement
while IFS= read -r line
do
    echo "$line"
    ((l++))
    if [[ $l -eq 10 ]]; then
        break
    fi
done < "${WORKSPACE}/CHANGELOG.md"


UPLOAD_VERSION="${UPLOAD_VERSION_MAJOR}.${UPLOAD_VERSION_MINOR}.${UPLOAD_VERSION_PATCH}.${UPLOAD_VERSION_BUILD}"

i=1

### file framework
while IFS= read -r line
do
    sed -i "s/aes.version.number: 0.1.0/aes.version.number: ${UPLOAD_VERSION}/g" "${WORKSPACE}/config/packages/framework.yaml"
    sed -i "s/aes.version.major: 0/aes.version.major: ${UPLOAD_VERSION_MAJOR}/g" "${WORKSPACE}/config/packages/framework.yaml"
    sed -i "s/aes.version.minor: 0/aes.version.minor: ${UPLOAD_VERSION_MINOR}/g" "${WORKSPACE}/config/packages/framework.yaml"
    sed -i "s/aes.version.patch: 0/aes.version.patch: ${UPLOAD_VERSION_PATCH}/g" "${WORKSPACE}/config/packages/framework.yaml"
    sed -i "s/aes.version.build: 10/aes.version.build: ${UPLOAD_VERSION_BUILD}/g" "${WORKSPACE}/config/packages/framework.yaml"
    ((i++))
    if [[ $i -eq 22 ]]; then
        break
    fi
done < "${WORKSPACE}/config/packages/framework.yaml"

#Set variables
l=1
# checking the changement
while IFS= read -r line
do
    echo "$line"
    ((l++))
    if [[ $l -eq 22 ]]; then
        break
    fi
done < "${WORKSPACE}/config/packages/framework.yaml"



#Set variables
i=1

#Read in template one line at the time, and replace variables (more
#natural (and efficient) way, thanks to Jonathan Leffler).
while IFS= read -r line
do
    sed -i "s/version: '1.0.0'/version: '${UPLOAD_VERSION}'/g" "${WORKSPACE}/config/packages/api_platform.yaml"
    ((i++))
    if [[ $i -eq 20 ]]; then
        break
    fi
done < "${WORKSPACE}/config/packages/api_platform.yaml"

#Set variables
l=1
# checking the changement
while IFS= read -r line
do
    echo "$line"
    ((l++))
    if [[ $l -eq 20 ]]; then
        break
    fi
done < "${WORKSPACE}/config/packages/api_platform.yaml"