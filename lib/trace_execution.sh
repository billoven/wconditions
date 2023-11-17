#!/bin/bash

# Set the locale to English with UTF-8 encoding
export LANG=en_US.UTF-8

# Extract the script name, host name, and first IP address
script_name=$(basename "$0")
host_name=$(hostname)
ip_address=$(hostname -I | awk '{print $1}')

# Function to get the system's time zone with GMT offset
get_system_timezone() {
    local timezone
    # Use timedatectl to retrieve the system's time zone
    timezone=$(timedatectl | grep "Time zone:" | awk '{print $3}')
    local gmt_offset
    # Use date to get the current GMT offset
    gmt_offset=$(date +"%:z")
    echo "$timezone (GMT$gmt_offset)"
}

# Execute get_system_timezone only once for the whole script
timezone=$(get_system_timezone)

# Function to display the state of execution steps
display_step_state() {
    local step_name="$1"
    local step_state="$2"
    
    # Display the step state with timestamp and time zone
    echo "[$(date +"%Y-%m-%d %H:%M:%S") $timezone] $step_name - $step_state"
}

# Function to display the duration of a step
display_duration() {
    local start_time=$1
    local step_name=$2
    local end_time=$(date +%s.%N)
    local duration=$(echo "$end_time - $start_time" | bc)

    # Display the duration with timestamp and time zone
    echo "[$(date +"%Y-%m-%d %H:%M:%S") ${timezone}] $step_name - $(printf "Duration: %.2f" $duration) seconds"
}

# Function to execute the steps
execute_step() {
    local step_name="$1"
    local step_function="$2"
    local start_time
    local end_time
    
    start_time=$(date +%s.%N)
    echo ""
    display_step_state  "$step_name" "Starting ..."

    # Execute the step's code by calling the specified function
    "$step_function"

    display_step_state  "$step_name" "Completed."
    end_time=$(date +%s)
    display_duration "$start_time" "$step_name"
}
