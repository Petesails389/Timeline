#!/bin/bash

# Script for backup 5 days rotation

data_file=$1
timestamp=$(date '+%y-%m-%d-%H-%M-%S')
backup_file=$2

if [ $# -eq 0 ]; then
        echo "./bash File_path backup_file_path"
        exit 1
fi

create_backup(){
       cp "${data_file}" "${backup_file}/backups_${timestamp}.backup" 
        if [ $? -eq 0 ]; then
                echo "Backup created"
        fi
}

perform_rotation(){
        backups=($(ls -t "${backup_file}/backups_"*.backup 2>/dev/null))

        if [ "${#backups[@]}" -gt 5 ]; then
                remove_backup=("${backups[@]:5}")
                for backup in "${remove_backup[@]}";
                do
                        rm -f "${backup}"
                done
        fi
}

create_backup
perform_rotation