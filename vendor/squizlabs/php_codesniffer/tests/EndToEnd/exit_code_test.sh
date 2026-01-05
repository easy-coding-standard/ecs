#!/usr/bin/env bash

function tear_down() {
  rm -f tests/EndToEnd/Fixtures/*.fixed
}

function test_phpcs_exit_code_clean_file() {
  bin/phpcs --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassOneWithoutStyleError.inc
  assert_exit_code 0
}
function test_phpcs_exit_code_clean_stdin() {
  bin/phpcs --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist < tests/EndToEnd/Fixtures/ClassOneWithoutStyleError.inc
  assert_exit_code 0
}
function test_phpcbf_exit_code_clean_file() {
  bin/phpcbf --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassOneWithoutStyleError.inc
  assert_exit_code 0
}
function test_phpcbf_exit_code_clean_stdin() {
  bin/phpcbf --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist < tests/EndToEnd/Fixtures/ClassOneWithoutStyleError.inc
  assert_exit_code 0
}

function test_phpcs_exit_code_fixable_file() {
  bin/phpcs --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassWithStyleError.inc
  assert_exit_code 1
}
function test_phpcs_exit_code_fixable_stdin() {
  bin/phpcs --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist < tests/EndToEnd/Fixtures/ClassWithStyleError.inc
  assert_exit_code 1
}
function test_phpcbf_exit_code_fixable_file() {
  bin/phpcbf --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassWithStyleError.inc
  assert_exit_code 0
}
function test_phpcbf_exit_code_fixable_stdin() {
  bin/phpcbf --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist < tests/EndToEnd/Fixtures/ClassWithStyleError.inc
  assert_exit_code 0
}

function test_phpcs_exit_code_non_fixable_file() {
  bin/phpcs --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassWithUnfixableStyleError.inc
  assert_exit_code 2
}
function test_phpcs_exit_code_non_fixable_stdin() {
  bin/phpcs --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist < tests/EndToEnd/Fixtures/ClassWithUnfixableStyleError.inc
  assert_exit_code 2
}
function test_phpcbf_exit_code_non_fixable_file() {
  bin/phpcbf --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassWithUnfixableStyleError.inc
  assert_exit_code 2
}
function test_phpcbf_exit_code_non_fixable_stdin() {
  bin/phpcbf --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist < tests/EndToEnd/Fixtures/ClassWithUnfixableStyleError.inc
  assert_exit_code 2
}

function test_phpcs_exit_code_fixable_and_non_fixable_file() {
  bin/phpcs --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassWithTwoStyleErrors.inc
  assert_exit_code 3
}
function test_phpcs_exit_code_fixable_and_non_fixable_stdin() {
  bin/phpcs --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist < tests/EndToEnd/Fixtures/ClassWithTwoStyleErrors.inc
  assert_exit_code 3
}
function test_phpcbf_exit_code_fixable_and_non_fixable_file() {
  bin/phpcbf --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassWithTwoStyleErrors.inc
  assert_exit_code 2
}
function test_phpcbf_exit_code_fixable_and_non_fixable_stdin() {
  bin/phpcbf --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist < tests/EndToEnd/Fixtures/ClassWithTwoStyleErrors.inc
  assert_exit_code 2
}
