#!/bin/bash

# Test Runner Script for E-Library Application

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  E-Library Test Runner${NC}"
echo -e "${GREEN}========================================${NC}"

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}Installing dependencies...${NC}"
    composer install
fi

# Function to run tests
run_tests() {
    local suite=$1
    local description=$2
    
    echo ""
    echo -e "${YELLOW}Running ${description}...${NC}"
    echo "----------------------------------------"
    
    if [ -z "$suite" ]; then
        vendor/bin/phpunit --testdox
    else
        vendor/bin/phpunit --testsuite "$suite" --testdox
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ ${description} passed!${NC}"
    else
        echo -e "${RED}✗ ${description} failed!${NC}"
        exit 1
    fi
}

# Parse command line arguments
case "${1:-all}" in
    unit)
        run_tests "unit" "Unit Tests"
        ;;
    feature)
        run_tests "feature" "Feature Tests"
        ;;
    coverage)
        echo -e "${YELLOW}Running tests with coverage...${NC}"
        vendor/bin/phpunit --coverage-html build/coverage --coverage-text
        echo -e "${GREEN}Coverage report generated in build/coverage/index.html${NC}"
        ;;
    watch)
        echo -e "${YELLOW}Watching for changes...${NC}"
        if command -v fswatch &> /dev/null; then
            fswatch -o app/ tests/ | while read f; do
                clear
                echo -e "${YELLOW}Files changed, running tests...${NC}"
                vendor/bin/phpunit --testdox
            done
        else
            echo -e "${RED}fswatch not found. Install it with: brew install fswatch${NC}"
            exit 1
        fi
        ;;
    all)
        run_tests "" "All Tests"
        ;;
    *)
        echo "Usage: $0 {unit|feature|coverage|watch|all}"
        echo ""
        echo "Commands:"
        echo "  unit     - Run unit tests only"
        echo "  feature  - Run feature tests only"
        echo "  coverage - Run tests with code coverage"
        echo "  watch    - Watch for changes and re-run tests"
        echo "  all      - Run all tests (default)"
        exit 1
        ;;
esac

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Test suite completed successfully!${NC}"
echo -e "${GREEN}========================================${NC}"
