#!/usr/bin/env python3
"""
Run Omni Dimension Service
Python script to start the service with proper environment variables
"""

import os
import sys
import subprocess

# Set environment variables
os.environ['OMNIDIMENSION_API_KEY'] = '-oh1U47bH-Xf806Y7UA1Mddfyi4xEmTyDI8A5uOdC58'
os.environ['WEBHOOK_URL'] = 'http://localhost/api/webhook.php'
os.environ['PHP_API_URL'] = 'http://localhost'

print("=" * 50)
print("Starting Omni Dimension Service")
print("=" * 50)
print()
print(f"API Key: {os.environ['OMNIDIMENSION_API_KEY'][:20]}...")
print(f"Webhook URL: {os.environ['WEBHOOK_URL']}")
print()
print("Service will run on: http://localhost:8000")
print("Press Ctrl+C to stop")
print()
print("-" * 50)
print()

# Change to script directory
script_dir = os.path.dirname(os.path.abspath(__file__))
os.chdir(script_dir)

# Run the service script as a subprocess with the environment variables
try:
    # Use subprocess to run the service with the environment
    subprocess.run([sys.executable, 'omnidimension_service.py'], env=os.environ)
except KeyboardInterrupt:
    print("\n\nService stopped by user")
    sys.exit(0)
except Exception as e:
    print(f"\n\nERROR: Failed to start service: {e}")
    import traceback
    traceback.print_exc()
    print("\nPlease check:")
    print("1. Python dependencies are installed: pip install -r requirements.txt")
    print("2. API key is correct")
    print("3. Port 8000 is available")
    sys.exit(1)

