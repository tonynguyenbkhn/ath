#!/bin/bash

echo "Backup DB..."
wp db export backup.sql

echo "Delete revisions..."
wp post delete $(wp post list --post_type='revision' --format=ids) --force

echo "Delete trashed posts..."
wp post delete $(wp post list --post_status=trash --format=ids) --force

echo "Delete spam comments..."
wp comment delete $(wp comment list --status=spam --format=ids) --force

echo "Delete transients..."
wp transient delete --all

echo "Optimize DB..."
wp db optimize

echo "Done!"