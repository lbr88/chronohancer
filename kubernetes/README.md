c# Kubernetes Deployment for Chronohancer

This directory contains Kubernetes configuration files for deploying the Chronohancer application.

## Components

- **namespace.yaml**: Creates a dedicated Kubernetes namespace for the application
- **configmap.yaml**: Contains non-sensitive configuration values
- **secret.yaml**: Contains sensitive information (passwords, keys)
- **storage.yaml**: Persistent volume claims for data storage
- **mysql.yaml**: MySQL database deployment and service
- **redis.yaml**: Redis cache deployment and service
- **nginx-config.yaml**: Nginx web server configuration
- **app.yaml**: Main application deployment with PHP-FPM and Nginx
- **ingress.yaml**: Ingress configuration for external access
- **Dockerfile**: Instructions for building the application container
- **deploy.sh**: Deployment script

## Prerequisites

- Kubernetes cluster
- kubectl configured to connect to your cluster
- Docker installed locally
- Container registry access (if deploying to a remote cluster)

## Deployment Instructions

1. **Update Configuration**

   Before deploying, update the following files with your specific settings:

   - **configmap.yaml**: Update environment variables
   - **secret.yaml**: Replace placeholder base64-encoded values with actual secrets
   - **ingress.yaml**: Update the hostname

2. **Build and Deploy**

   Run the deployment script:

   ```bash
   ./kubernetes/deploy.sh
   ```

   This script will:
   - Build the Docker image
   - Apply all Kubernetes configurations
   - Wait for deployments to be ready

3. **Manual Deployment**

   If you prefer to deploy manually:

   ```bash
   # Build the Docker image
   docker build -t chronohancer:latest -f kubernetes/Dockerfile .

   # Apply Kubernetes configurations
   kubectl apply -f kubernetes/namespace.yaml
   kubectl apply -f kubernetes/configmap.yaml
   kubectl apply -f kubernetes/secret.yaml
   kubectl apply -f kubernetes/storage.yaml
   kubectl apply -f kubernetes/nginx-config.yaml
   kubectl apply -f kubernetes/mysql.yaml
   kubectl apply -f kubernetes/redis.yaml
   kubectl apply -f kubernetes/app.yaml
   kubectl apply -f kubernetes/ingress.yaml
   ```

## Accessing the Application

Once deployed, the application will be available at the hostname specified in the ingress configuration (default: chronohancer.example.com).

## Monitoring and Troubleshooting

```bash
# Check deployment status
kubectl -n chronohancer get deployments

# Check pods status
kubectl -n chronohancer get pods

# View logs for a specific pod
kubectl -n chronohancer logs <pod-name>

# Execute commands in a pod
kubectl -n chronohancer exec -it <pod-name> -- /bin/bash
```

## Scaling

To scale the application horizontally:

```bash
kubectl -n chronohancer scale deployment/chronohancer-app --replicas=3