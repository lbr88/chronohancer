variable "NGINX_TAGS" {
  default = ""
}

variable "NGINX_LABELS" {
  default = ""
}

variable "PHP_FPM_TAGS" {
  default = ""
}

variable "PHP_FPM_LABELS" {
  default = ""
}

group "default" {
  targets = ["php-fpm", "nginx"]
}

target "php-fpm" {
  dockerfile = "common/php-fpm/Dockerfile"
  target = "production"
  context = "."
  tags = split(",", PHP_FPM_TAGS)
  labels = split(",", PHP_FPM_LABELS)
}

target "nginx" {
  dockerfile = "production/nginx/Dockerfile"
  context = "."
  tags = split(",", NGINX_TAGS)
  labels = split(",", NGINX_LABELS)
}