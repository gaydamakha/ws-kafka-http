create table if not exists timestamps
(
    session_id int not null,
    ms1_timestamp timestamp(6) not null default '0000-00-00 00:00:00.000000',
    ms2_timestamp timestamp(6) not null default '0000-00-00 00:00:00.000000',
    ms3_timestamp timestamp(6) not null default '0000-00-00 00:00:00.000000',
    end_timestamp timestamp(6) not null default '0000-00-00 00:00:00.000000',
    finished tinyint not null default 0
);
