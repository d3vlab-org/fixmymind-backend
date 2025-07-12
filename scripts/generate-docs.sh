#!/bin/bash

# Generate Swagger documentation and copy to public folder
echo "Generating Swagger API documentation..."

# Generate the documentation
php artisan l5-swagger:generate

# Copy to public docs folder
echo "Copying documentation to public/docs/..."
cp storage/api-docs/api-docs.json public/docs/

echo "API documentation generated successfully!"
echo "Documentation is available at:"
echo "- Laravel route: /docs (requires basic auth: admin:secret123)"
echo "- Static version: /docs/index.html (for direct access)"
echo ""
echo "To update production deployment, upload the public/docs/ folder to your server."

