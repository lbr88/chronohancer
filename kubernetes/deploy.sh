#!/bin/bash

# Exit on error
set -e

# Build the Docker image
echo "Building Docker image..."
docker build -t chronohancer:latest -f kubernetes/Dockerfile .

# Push to your container registry (uncomment and modify as needed)
# echo "Pushing to container registry..."
# docker tag chronohancer:latest your-registry/chronohancer:latest
# docker push your-registry/chronohancer:latest

# Apply Kubernetes configurations
echo "Applying Kubernetes configurations..."
kubectl apply -f kubernetes/namespace.yaml
kubectl apply -f kubernetes/configmap.yaml
kubectl apply -f kubernetes/secret.yaml
kubectl apply -f kubernetes/storage.yaml
kubectl apply -f kubernetes/nginx-config.yaml
kubectl apply -f kubernetes/mysql.yaml
kubectl apply -f kubernetes/redis.yaml
kubectl apply -f kubernetes/app.yaml
kubectl apply -f kubernetes/ingress.yaml

echo "Waiting for deployments to be ready..."
kubectl -n chronohancer rollout status deployment/chronohancer-mysql
kubectl -n chronohancer rollout status deployment/chronohancer-redis
kubectl -n chronohancer rollout status deployment/chronohancer-app

echo "Deployment completed successfully!"
echo "Your application should be available at: https://chronohancer.example.com"