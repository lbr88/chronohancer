#!/bin/bash

# Function to validate version format (vX.Y.Z)
validate_version() {
    if ! [[ $1 =~ ^v[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "Error: Version must be in format vX.Y.Z (e.g., v1.0.0)"
        exit 1
    fi
}

# Function to get the latest tag
get_latest_tag() {
    git fetch --tags > /dev/null 2>&1
    latest_tag=$(git describe --tags --abbrev=0 2>/dev/null)
    
    if [ -z "$latest_tag" ]; then
        echo "No tags found in this repository."
    else
        echo "Latest tag: $latest_tag"
    fi
}

# Function to create and push a new tag
create_tag() {
    version=$1
    validate_version "$version"
    
    echo "Creating tag $version..."
    
    # Check if tag already exists
    if git rev-parse "$version" >/dev/null 2>&1; then
        echo "Error: Tag $version already exists."
        exit 1
    fi
    
    # Create and push the tag
    git tag "$version"
    git push origin "$version"
    
    echo "Tag $version created and pushed successfully."
    echo "GitHub Actions will automatically:"
    echo "  - Create a release based on this tag"
    echo "  - Build and tag the Docker image with version $version"
}

# Main script logic
if [ $# -eq 0 ]; then
    # No arguments provided, show latest tag
    get_latest_tag
else
    # Argument provided, create and push tag
    create_tag "$1"
fi