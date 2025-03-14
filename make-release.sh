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

# Function to update Helm chart version
update_helm_chart_version() {
    version=$1
    # Remove 'v' prefix for Helm chart version
    chart_version=${version#v}
    
    echo "Updating Helm chart version to $chart_version..."
    
    # Update Chart.yaml version
    sed -i "s/^version:.*/version: $chart_version/" helm/chronohancer/Chart.yaml
    sed -i "s/^appVersion:.*/appVersion: \"$chart_version\"/" helm/chronohancer/Chart.yaml
    
    echo "Helm chart version updated successfully."
}

# Function to package Helm chart
package_helm_chart() {
    version=$1
    
    echo "Packaging Helm chart..."
    
    # Create directories for chart release
    mkdir -p .cr-release-packages/ .cr-index
    
    # Package the Helm chart
    helm package helm/chronohancer -d .cr-release-packages/
    
    # Create index file
    helm repo index .cr-release-packages/ --url https://lbr88.github.io/chronohancer/
    
    echo "Helm chart packaged successfully."
}

# Function to update GitHub Pages repository
update_github_pages() {
    version=$1
    
    echo "Updating GitHub Pages repository..."
    
    # Save current branch to return to it later
    current_branch=$(git rev-parse --abbrev-ref HEAD)
    
    # Stash any changes in the working directory
    git stash
    
    # Switch to gh-pages branch
    git checkout gh-pages
    
    # Copy the packaged chart and index to the root directory
    cp .cr-release-packages/*.tgz .
    cp .cr-release-packages/index.yaml .
    
    # Add, commit, and push the changes
    git add *.tgz index.yaml
    git commit -m "Update Helm chart to $version"
    git push origin gh-pages
    
    # Switch back to the original branch
    git checkout $current_branch
    
    # Apply stashed changes if any
    git stash pop 2>/dev/null || true
    
    echo "GitHub Pages repository updated successfully."
}

# Function to clean up temporary files
cleanup() {
    echo "Cleaning up temporary files..."
    
    # Remove temporary directories
    rm -rf .cr-release-packages .cr-index
    
    echo "Cleanup completed."
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
    
    # Update Helm chart version
    update_helm_chart_version "$version"
    
    # Commit the Helm chart version update
    git add helm/chronohancer/Chart.yaml
    git commit -m "Update Helm chart version to ${version#v}"
    
    # Create and push the tag
    git tag "$version"
    git push origin "$version"
    git push origin master
    
    # Package Helm chart
    package_helm_chart "$version"
    
    # Update GitHub Pages repository
    update_github_pages "$version"
    
    # Clean up temporary files
    cleanup
    
    echo "Tag $version created and pushed successfully."
    echo "GitHub Actions will automatically:"
    echo "  - Create a release based on this tag"
    echo "  - Build and tag the Docker image with version $version"
    
    echo ""
    echo "Helm chart repository has been updated at https://lbr88.github.io/chronohancer/"
}

# Main script logic
if [ $# -eq 0 ]; then
    # No arguments provided, show latest tag
    get_latest_tag
else
    # Argument provided, create and push tag
    create_tag "$1"
fi