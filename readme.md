Configuration Library
======================

[![Build Status](https://travis-ci.org/ckressibucher/config.svg)](https://travis-ci.org/ckressibucher/config)

This package provides a class to manage hierarchical data.
A common usecase is configuration data.

Note that this package does not provide any readers or writers
for config files in different formats. Instead it is initialized
with an array (and internally works with an array). There are
plenty of other libraries which may help to convert a config file
into an array.
