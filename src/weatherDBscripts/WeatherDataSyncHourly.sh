#!/bin/bash

# Authentications access to MySQL DBs is done with ~/.my.cnf
# This must be configured on Prod server and Backup server
# with the user account that will execute this script
#[client]
# user=your_username
# password=your_password

# Add library to trace execution steps
# Functions available : "display_step_state", "execute_step", "display_duration"
source ../../lib/trace_execution.sh

# Database configurations
source_server="192.168.17.10"
dest_server="192.168.17.20"
backup_source_path="/home/pierre/backup/wconditions"
backup_dest_path="/home/pierre/backup/wconditions"
databases=("VillebonWeatherReport" "BethuneWeatherReport")

check_databases_access() {
    total_databases=${#databases[@]}
    current_database=0

    for db in "${databases[@]}"; do
        current_database=$((current_database + 1))
        
        if ! mysql -h "$source_server" -e "use $db"; then
            echo -e "\nError: Unable to access [$db] on source server: [$source_server]. Exiting."
            exit 1
        fi

        if ! mysql -h "$dest_server" -e "use $db"; then
            echo -e "\nError: Unable to access [$db] on destination server [$dest_server]. Exiting."
            exit 1
        fi
    done

    echo -e "\nDatabase access check completed."
}

dump_databases() {
    total_databases=${#databases[@]}
    current_database=0

    for db in "${databases[@]}"; do
        current_database=$((current_database + 1))

        current_time=$(date +"%Y%m%d%H%M%S")
        backup_file="$db-$current_time.sql"

        ssh_result=$(ssh $source_server "mysqldump --single-transaction --flush-logs --databases $db > $backup_source_path/$backup_file")
        ssh_status=$?

        if [ $ssh_status -eq 0 ]; then
            echo -e "\nSSH command for $db executed successfully."
        else
            echo -e "\nError: SSH command for $db failed with error code $ssh_status"
            exit 1
        fi
    done
}

transfer_dump() {
    total_databases=${#databases[@]}
    current_database=0
    
    for db in "${databases[@]}"; do
        current_database=$((current_database + 1))
        echo " ============ On s'occupe de : $db qui se trouve dans $source_server:$backup_source_path/$backup_file"
        scp_result=$(scp $source_server:$backup_source_path/$backup_file $backup_dest_path)
        scp_status=$?

        if [ $scp_status -eq 0 ]; then
            echo -e "SCP command for $db dump $source_server:$backup_source_path/$backup_file $backup_dest_path executed successfully."
        else
            echo -e "\nError: SCP command for $db failed with error code $scp_status"
            exit 1
        fi
    done
}

restore_databases() {
    total_databases=${#databases[@]}
    current_database=0
    
    for db in "${databases[@]}"; do
        current_database=$((current_database + 1))
        mysql_result=$(mysql -h $dest_server $db < $backup_dest_path/$backup_file)
        mysql_status=$?

        if [ $mysql_status -eq 0 ]; then
            echo -e "MySQL restore for $db executed successfully."
        else
            echo -e "\nError: MySQL restore for $db failed with error code $mysql_status"
            exit 1
        fi
    done
}

# Example usage in a script
main() {
    local script_start_time
    script_start_time=$(date +%s.%N)

    echo ""
    display_step_state  "$script_name" "Starting ..."
    execute_step "Check databases access" "check_databases_access"
    execute_step "Dump Mysql databases content" "dump_databases"
    execute_step "Transfer databases dumps on new mysql server" "transfer_dump"
    execute_step "Restore databases on new mysql server" "restore_databases"
    echo ""
    display_step_state  "$script_name" "Completed."
    display_duration "$script_start_time" "$script_name:$ip_address"
}

main
