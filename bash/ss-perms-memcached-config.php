#!/bin/bash

####################################################################################################
#### author: SlickStack ############################################################################
#### link: https://slickstack.io ###################################################################
#### mirror: https://mirrors.slickstack.io/bash/ss-perms-memcached-config.txt ######################
#### path: /var/www/ss-perms-memcached-config ######################################################
#### destination: n/a (not a boilerplate) ##########################################################
#### purpose: Resets file and user permissions for Memcached module configuration files ############
#### module version: Memcached 1.5.x ###############################################################
#### sourced by: ss-install-memcached-config, ss-perms #############################################
####################################################################################################

## source ss-config ##
source /var/www/ss-config

## source ss-functions ##
source /var/www/ss-functions

## BELOW THIS RELIES ON SS-CONFIG AND SS-FUNCTIONS

####################################################################################################
#### TABLE OF CONTENTS (SS-Perms-Memcached-Config) #################################################
####################################################################################################

## this is a brief summary of the different code snippets you will find in this script ##
## each section should be commented so you understand what is being accomplished ##

## A. Touch Timestamp File
## B. Message (Begin Script)
## C. Reset Permissions (Nginx Config)

####################################################################################################
#### A. SS-Perms-Memcached-Config: Touch Timestamp File ############################################
####################################################################################################

## this is a dummy timestamp file that will remember the last time this script was run ##
## it can be useful for developer reference and is sometimes used by SlickStack ##

## script timestamp ##
ss_touch "${TIMESTAMP_SS_PERMS_MEMCACHED_CONFIG}"

####################################################################################################
#### B. SS-Perms-Memcached-Config: Message (Begin Script) ##########################################
####################################################################################################

## this is a simple message that announces to the shell the purpose of this bash script ##
## it will only be noticed by sudo users who manually call ss core bash scripts ##

## echo message ##
ss_echo "${COLOR_INFO}Running ss-perms-memcached-config... ${COLOR_RESET}"

####################################################################################################
#### C. SS-Perms-Memcached-Config: Reset Permissions (Memcached Config) ############################
####################################################################################################

## although all permissions are reset in ss-perms we do it on per-module basis as well ##
## this ensures correct permissions in case this script is run individually etc ##





####################################################################################################
#### SlickStack: Reset Permissions (SlickStack Scripts) ############################################
####################################################################################################

## we include this permissions reset in all cron jobs and bash scripts for redundancy ##
## chmod 0700 means only the root/sudo users can execute any SlickStack scripts ##

## THIS SNIPPET DOES NOT RELY ON SS-CONFIG OR SS-FUNCTIONS
## SNIPPET: ss bash scripts, ss cron jobs
## UPDATED: 02JUL2022

chown root:root /var/www/ss* ## must be root:root
chown root:root /var/www/crons/*cron* ## must be root:root
chown root:root /var/www/crons/custom/*cron* ## must be root:root
chmod 0700 /var/www/ss* ## 0700 means only root/sudo can execute
chmod 0700 /var/www/crons/*cron* ## 0700 means only root/sudo can execute
chmod 0700 /var/www/crons/custom/*cron* ## 0700 means only root/sudo can execute

####################################################################################################
#### SlickStack: External References Used To Improve This Script (Thanks, Interwebz) ###############
####################################################################################################

## Ref: 

## SS_EOF
