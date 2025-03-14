{{/*
Expand the name of the chart.
*/}}
{{- define "chronohancer.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Create a default fully qualified app name.
We truncate at 63 chars because some Kubernetes name fields are limited to this (by the DNS naming spec).
If release name contains chart name it will be used as a full name.
*/}}
{{- define "chronohancer.fullname" -}}
{{- if .Values.fullnameOverride }}
{{- .Values.fullnameOverride | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- $name := default .Chart.Name .Values.nameOverride }}
{{- if contains $name .Release.Name }}
{{- .Release.Name | trunc 63 | trimSuffix "-" }}
{{- else }}
{{- printf "%s-%s" .Release.Name $name | trunc 63 | trimSuffix "-" }}
{{- end }}
{{- end }}
{{- end }}

{{/*
Create chart name and version as used by the chart label.
*/}}
{{- define "chronohancer.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Common labels
*/}}
{{- define "chronohancer.labels" -}}
helm.sh/chart: {{ include "chronohancer.chart" . }}
{{ include "chronohancer.selectorLabels" . }}
{{- if .Chart.AppVersion }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
{{- end }}

{{/*
Selector labels
*/}}
{{- define "chronohancer.selectorLabels" -}}
app.kubernetes.io/name: {{ include "chronohancer.name" . }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end }}

{{/*
Create the name of the service account to use
*/}}
{{- define "chronohancer.serviceAccountName" -}}
{{- if .Values.serviceAccount.create }}
{{- default (include "chronohancer.fullname" .) .Values.serviceAccount.name }}
{{- else }}
{{- default "default" .Values.serviceAccount.name }}
{{- end }}
{{- end }}

{{/*
MySQL fullname
*/}}
{{- define "chronohancer.mysql.fullname" -}}
{{- printf "%s-mysql" (include "chronohancer.fullname" .) | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
Redis fullname
*/}}
{{- define "chronohancer.redis.fullname" -}}
{{- printf "%s-redis" (include "chronohancer.fullname" .) | trunc 63 | trimSuffix "-" }}
{{- end }}

{{/*
App fullname
*/}}
{{- define "chronohancer.app.fullname" -}}
{{- printf "%s-app" (include "chronohancer.fullname" .) | trunc 63 | trimSuffix "-" }}
{{- end }}