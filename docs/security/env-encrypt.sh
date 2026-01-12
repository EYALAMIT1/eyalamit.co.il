#!/bin/bash
# Environment Encryption Script
# Encrypts and decrypts .env files for secure deployment
#
# Usage:
#   ./env-encrypt.sh encrypt .env.production
#   ./env-encrypt.sh decrypt .env.production.enc

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
ENV_KEY_FILE="$PROJECT_ROOT/.env.key"
BACKUP_DIR="$PROJECT_ROOT/backups/env"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" >&2
}

success() {
    echo -e "${GREEN}[SUCCESS] $1${NC}"
}

warning() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

# Check if openssl is available
check_dependencies() {
    if ! command -v openssl &> /dev/null; then
        error "OpenSSL is required but not installed. Please install it first."
        exit 1
    fi
}

# Generate encryption key
generate_key() {
    if [ ! -f "$ENV_KEY_FILE" ]; then
        log "Generating new encryption key..."
        openssl rand -base64 32 > "$ENV_KEY_FILE"
        chmod 600 "$ENV_KEY_FILE"
        success "Encryption key generated: $ENV_KEY_FILE"
        warning "Keep this key secure and don't commit it to version control!"
    else
        log "Using existing encryption key"
    fi
}

# Validate environment file
validate_env_file() {
    local file="$1"

    if [ ! -f "$file" ]; then
        error "Environment file not found: $file"
        exit 1
    fi

    # Basic validation - check for required variables
    local required_vars=("DB_NAME" "DB_USER" "DB_PASSWORD" "WP_HOME" "WP_SITEURL")
    local missing_vars=()

    for var in "${required_vars[@]}"; do
        if ! grep -q "^$var=" "$file"; then
            missing_vars+=("$var")
        fi
    done

    if [ ${#missing_vars[@]} -ne 0 ]; then
        warning "Missing required variables: ${missing_vars[*]}"
        read -p "Continue anyway? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
}

# Encrypt environment file
encrypt_env() {
    local input_file="$1"
    local output_file="${input_file}.enc"
    local key_file="$ENV_KEY_FILE"

    if [ ! -f "$key_file" ]; then
        error "Encryption key not found. Run 'generate_key' first."
        exit 1
    fi

    validate_env_file "$input_file"

    # Create backup
    local backup_file="$BACKUP_DIR/$(basename "$input_file").$(date +%Y%m%d_%H%M%S).backup"
    cp "$input_file" "$backup_file"
    log "Backup created: $backup_file"

    # Encrypt
    log "Encrypting $input_file..."
    local encryption_key=$(cat "$key_file")

    if openssl enc -aes-256-cbc -salt -in "$input_file" -out "$output_file" -k "$encryption_key"; then
        success "Environment file encrypted: $output_file"
        log "Original file kept at: $input_file (consider removing it)"

        # Show file sizes
        local original_size=$(stat -f%z "$input_file" 2>/dev/null || stat -c%s "$input_file" 2>/dev/null)
        local encrypted_size=$(stat -f%z "$output_file" 2>/dev/null || stat -c%s "$output_file" 2>/dev/null)
        log "Original: $original_size bytes, Encrypted: $encrypted_size bytes"
    else
        error "Encryption failed"
        exit 1
    fi
}

# Decrypt environment file
decrypt_env() {
    local input_file="$1"
    local key_file="$ENV_KEY_FILE"

    if [ ! -f "$key_file" ]; then
        error "Encryption key not found: $key_file"
        exit 1
    fi

    if [ ! -f "$input_file" ]; then
        error "Encrypted file not found: $input_file"
        exit 1
    fi

    # Determine output file name
    local output_file="${input_file%.enc}"

    if [ -f "$output_file" ]; then
        warning "Output file already exists: $output_file"
        read -p "Overwrite? (y/N): " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi

    # Decrypt
    log "Decrypting $input_file..."
    local encryption_key=$(cat "$key_file")

    if openssl enc -aes-256-cbc -d -in "$input_file" -out "$output_file" -k "$encryption_key"; then
        success "Environment file decrypted: $output_file"
        chmod 644 "$output_file"
    else
        error "Decryption failed. Check your encryption key."
        exit 1
    fi
}

# Test encryption/decryption
test_encryption() {
    local test_file="$1"

    if [ ! -f "$test_file" ]; then
        error "Test file not found: $test_file"
        exit 1
    fi

    log "Testing encryption/decryption..."

    # Encrypt
    local encrypted_file="${test_file}.test.enc"
    local encryption_key=$(cat "$ENV_KEY_FILE")

    openssl enc -aes-256-cbc -salt -in "$test_file" -out "$encrypted_file" -k "$encryption_key"

    # Decrypt
    local decrypted_file="${encrypted_file}.decrypted"
    openssl enc -aes-256-cbc -d -in "$encrypted_file" -out "$decrypted_file" -k "$encryption_key"

    # Compare
    if cmp -s "$test_file" "$decrypted_file"; then
        success "Encryption test passed!"
    else
        error "Encryption test failed!"
        exit 1
    fi

    # Cleanup
    rm -f "$encrypted_file" "$decrypted_file"
}

# Show usage
usage() {
    echo "Environment Encryption Script"
    echo "Usage: $0 <command> [file]"
    echo ""
    echo "Commands:"
    echo "  generate-key          Generate new encryption key"
    echo "  encrypt <file>        Encrypt environment file"
    echo "  decrypt <file>        Decrypt environment file"
    echo "  test <file>           Test encryption/decryption"
    echo "  help                  Show this help"
    echo ""
    echo "Examples:"
    echo "  $0 generate-key"
    echo "  $0 encrypt .env.production"
    echo "  $0 decrypt .env.production.enc"
    echo "  $0 test .env.production"
}

# Main script
main() {
    check_dependencies

    case "${1:-help}" in
        "generate-key")
            generate_key
            ;;
        "encrypt")
            if [ -z "$2" ]; then
                error "Please specify a file to encrypt"
                usage
                exit 1
            fi
            generate_key
            encrypt_env "$2"
            ;;
        "decrypt")
            if [ -z "$2" ]; then
                error "Please specify a file to decrypt"
                usage
                exit 1
            fi
            decrypt_env "$2"
            ;;
        "test")
            if [ -z "$2" ]; then
                error "Please specify a file to test"
                usage
                exit 1
            fi
            generate_key
            test_encryption "$2"
            ;;
        "help"|*)
            usage
            ;;
    esac
}

# Run main function with all arguments
main "$@"