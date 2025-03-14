# Chronohancer Helm Chart

This Helm chart deploys the Chronohancer Laravel application on a Kubernetes cluster.

## Prerequisites

- Kubernetes 1.19+
- Helm 3.2.0+
- PV provisioner support in the underlying infrastructure
- LoadBalancer support or Ingress Controller

## Installing the Chart

To install the chart with the release name `chronohancer`:

```bash
helm install chronohancer ./helm/chronohancer
```

The command deploys Chronohancer on the Kubernetes cluster with default configuration. The [Parameters](#parameters) section lists the parameters that can be configured during installation.

## Uninstalling the Chart

To uninstall/delete the `chronohancer` deployment:

```bash
helm uninstall chronohancer
```

## Parameters

### Global parameters

| Name                  | Description                                     | Value                       |
| --------------------- | ----------------------------------------------- | --------------------------- |
| `global.domain`       | Domain name for the application                 | `chronohancer.example.com` |
| `global.env`          | Environment setting                             | `production`                |
| `global.tls.enabled`  | Enable TLS                                      | `true`                      |
| `global.tls.secretName` | TLS secret name                               | `chronohancer-tls`          |

### Common parameters

| Name                  | Description                                     | Value           |
| --------------------- | ----------------------------------------------- | --------------- |
| `nameOverride`        | String to partially override the chart name     | `""`            |
| `fullnameOverride`    | String to fully override the chart name         | `""`            |

### Namespace parameters

| Name                  | Description                                     | Value           |
| --------------------- | ----------------------------------------------- | --------------- |
| `namespace.create`    | Create a namespace for the deployment           | `true`          |
| `namespace.name`      | Name of the namespace                           | `chronohancer`  |

### Application parameters

| Name                           | Description                                     | Value           |
| ------------------------------ | ----------------------------------------------- | --------------- |
| `app.replicaCount`             | Number of replicas                              | `2`             |
| `app.image.repository`         | Application image repository                    | `chronohancer`  |
| `app.image.tag`                | Application image tag                           | `latest`        |
| `app.image.pullPolicy`         | Application image pull policy                   | `IfNotPresent`  |
| `app.resources.limits.cpu`     | CPU resource limits                             | `500m`          |
| `app.resources.limits.memory`  | Memory resource limits                          | `1Gi`           |
| `app.resources.requests.cpu`   | CPU resource requests                           | `250m`          |
| `app.resources.requests.memory`| Memory resource requests                        | `512Mi`         |
| `app.config.*`                 | Application configuration values                | See values.yaml |
| `app.secret.*`                 | Application secret values                       | See values.yaml |
| `app.storage.size`             | Storage size for application                    | `10Gi`          |
| `app.storage.storageClass`     | Storage class for application                   | `standard`      |

### Nginx parameters

| Name                           | Description                                     | Value           |
| ------------------------------ | ----------------------------------------------- | --------------- |
| `nginx.image.repository`       | Nginx image repository                          | `nginx`         |
| `nginx.image.tag`              | Nginx image tag                                 | `1.25`          |
| `nginx.image.pullPolicy`       | Nginx image pull policy                         | `IfNotPresent`  |
| `nginx.resources.limits.cpu`   | CPU resource limits                             | `300m`          |
| `nginx.resources.limits.memory`| Memory resource limits                          | `512Mi`         |
| `nginx.resources.requests.cpu` | CPU resource requests                           | `100m`          |
| `nginx.resources.requests.memory`| Memory resource requests                      | `256Mi`         |
| `nginx.config.serverName`      | Server name for Nginx                           | `_`             |
| `nginx.config.clientMaxBodySize`| Client max body size                           | `100m`          |

### MySQL parameters

| Name                           | Description                                     | Value           |
| ------------------------------ | ----------------------------------------------- | --------------- |
| `mysql.enabled`                | Enable MySQL                                    | `true`          |
| `mysql.image.repository`       | MySQL image repository                          | `mysql`         |
| `mysql.image.tag`              | MySQL image tag                                 | `8.0`           |
| `mysql.image.pullPolicy`       | MySQL image pull policy                         | `IfNotPresent`  |
| `mysql.resources.limits.cpu`   | CPU resource limits                             | `500m`          |
| `mysql.resources.limits.memory`| Memory resource limits                          | `1Gi`           |
| `mysql.resources.requests.cpu` | CPU resource requests                           | `250m`          |
| `mysql.resources.requests.memory`| Memory resource requests                      | `512Mi`         |
| `mysql.storage.size`           | Storage size for MySQL                          | `10Gi`          |
| `mysql.storage.storageClass`   | Storage class for MySQL                         | `standard`      |

### Redis parameters

| Name                           | Description                                     | Value           |
| ------------------------------ | ----------------------------------------------- | --------------- |
| `redis.enabled`                | Enable Redis                                    | `true`          |
| `redis.image.repository`       | Redis image repository                          | `redis`         |
| `redis.image.tag`              | Redis image tag                                 | `7.0`           |
| `redis.image.pullPolicy`       | Redis image pull policy                         | `IfNotPresent`  |
| `redis.resources.limits.cpu`   | CPU resource limits                             | `300m`          |
| `redis.resources.limits.memory`| Memory resource limits                          | `512Mi`         |
| `redis.resources.requests.cpu` | CPU resource requests                           | `100m`          |
| `redis.resources.requests.memory`| Memory resource requests                      | `256Mi`         |
| `redis.storage.size`           | Storage size for Redis                          | `5Gi`           |
| `redis.storage.storageClass`   | Storage class for Redis                         | `standard`      |

### Ingress parameters

| Name                           | Description                                     | Value           |
| ------------------------------ | ----------------------------------------------- | --------------- |
| `ingress.enabled`              | Enable ingress                                  | `true`          |
| `ingress.className`            | Ingress class name                              | `nginx`         |
| `ingress.annotations`          | Ingress annotations                             | See values.yaml |