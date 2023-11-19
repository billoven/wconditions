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
dumpfiles=()

check_databases_access() {
    total_databases=${#databases[@]}
    current_database=0

    for db in "${databases[@]}"; do
        
        if ! mysql -h "$source_server" -e "use $db"; then
            echo -e "\nError: Unable to access [$db] on source server: [$source_server]. Exiting."
            exit 1
        fi

        if ! mysql -h "$dest_server" -e "use $db"; then
            echo -e "\nError: Unable to access [$db] on destination server [$dest_server]. Exiting."
            exit 1
        fi
        current_database=$((current_database + 1))
    done

    echo -e "\nDatabase access check completed."
}

dump_databases() {
    total_databases=${#databases[@]}
    current_database=0

    for db in "${databases[@]}"; do

        current_time=$(date +"%Y%m%d%H%M%S")
        backup_file="$db-$current_time.sql"
        dumpfiles[$current_database]=$backup_file

        ssh_result=$(ssh $source_server "mysqldump --single-transaction --flush-logs --databases $db > $backup_source_path/$backup_file")
        ssh_status=$?

        if [ $ssh_status -eq 0 ]; then
            echo -e "\nSSH command for $db executed successfully."
        else
            echo -e "\nError: SSH command for $db failed with error code $ssh_status"
            exit 1
        fi

        current_database=$((current_database + 1))
    done
}

transfer_dump() {
    total_databases=${#databases[@]}
    current_database=0
    
    for db in "${databases[@]}"; do    
        backup_file=${dumpfiles[$current_database]}
        scp_result=$(scp $source_server:$backup_source_path/$backup_file $backup_dest_path)
        scp_status=$?

        if [ $scp_status -eq 0 ]; then
            echo -e "SCP command for $db dump $source_server:$backup_source_path/$backup_file $backup_dest_path executed successfully."
        else
            echo -e "\nError: SCP command for $db failed with error code $scp_status"
            exit 1
        fi
        current_database=$((current_database + 1))
    done
}

restore_databases() {
    total_databases=${#databases[@]}
    current_database=0
    
    for db in "${databases[@]}"; do
        backup_file=${dumpfiles[$current_database]}
        
        mysql_result=$(mysql -h $dest_server $db < $backup_dest_path/$backup_file)
        mysql_status=$?

        if [ $mysql_status -eq 0 ]; then
            echo -e "MySQL restore for $db executed successfully."
        else
            echo -e "\nError: MySQL restore for $db failed with error code $mysql_status"
            exit 1
        fi
        current_database=$((current_database + 1))
    done
}

remove_dump_files() {
    for db in "${databases[@]}"; do
        # Remove dump files from destination path
        dest_dump_file="$backup_dest_path/$db-*"
        rm -f $dest_dump_file
        echo "Removed dump file(s) for $db from $backup_dest_path"

        # Remove dump files from source path via ssh
        ssh $source_server "rm -f $backup_source_path/$db-*"
        echo "Removed dump file(s) for $db from $source_server:$backup_source_path"
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
    execute_step "Remove SQL dump files" "remove_dump_files"
    echo ""
    display_step_state  "$script_name" "Completed."
    display_duration "$script_start_time" "$script_name:$ip_address"
}

main
