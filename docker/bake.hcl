group "default" {
  targets = ["php-fpm", "nginx"]
}

target "php-fpm" {
  dockerfile = "common/php-fpm/Dockerfile"
  target = "production"
  context = "."
}

target "nginx" {
  dockerfile = "production/nginx/Dockerfile"
  context = "."
}