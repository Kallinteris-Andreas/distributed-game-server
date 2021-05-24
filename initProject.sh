#!/bin/bash
set -e
minikube start
minikube mount .:/hostprj &
minikube kubectl -- apply -f ./project.yaml
echo -e "\nYou can connect with your browser to the following link once all pods have started:"
minikube service --url userinterface
