# Stage 1: Build assets
FROM node:18 AS frontend-builder

# Set working directory
WORKDIR /var/www

# Copy the package.json and install dependencies
COPY package*.json ./
RUN npm install && \
    chmod +x node_modules/.bin/vite

# Copy the frontend source code
COPY vite.config.js ./
COPY resources /var/www/resources
COPY public /var/www/public

# Copy vendor directory (comes from build args)
ARG VENDOR_DIR=./vendor
COPY ${VENDOR_DIR} /var/www/vendor

# Build frontend assets
RUN npm run build

# Verify that the build directory and manifest.json exist
RUN ls -lash public/build && \
    if [ ! -f public/build/manifest.json ]; then \
    echo "Error: Vite manifest.json not found!" && exit 1; \
    else \
    echo "Vite manifest.json found successfully"; \
    fi

RUN ls -lash . && ls -lash public

# Stage 2: Nginx production image
FROM nginx:alpine

# Copy custom Nginx configuration
# -----------------------------------------------------------
# Replace the default Nginx configuration with our custom one
# that is optimized for serving a Laravel application.
# -----------------------------------------------------------
COPY ./docker/production/nginx/nginx.conf /etc/nginx/nginx.conf

# Copy Laravel's public assets from the builder stage
# -----------------------------------------------------------
# We only need the 'public' directory from our Laravel app.
# -----------------------------------------------------------
COPY --from=frontend-builder /var/www/public /var/www/public

# Set the working directory to the public folder
WORKDIR /var/www/public

# Expose port 80 and start Nginx
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
