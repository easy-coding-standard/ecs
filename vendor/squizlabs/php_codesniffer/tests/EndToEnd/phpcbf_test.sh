#!/usr/bin/env bash

function tear_down() {
  rm -rf tests/EndToEnd/Fixtures/*.fixed
}

function test_phpcbf_is_working() {
  OUTPUT="$( { bin/phpcbf --no-cache --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassOneWithoutStyleError.inc tests/EndToEnd/Fixtures/ClassTwoWithoutStyleError.inc; } 2>&1 )"

  assert_successful_code
  assert_contains "No violations were found" "$OUTPUT"
}

function test_phpcbf_is_working_in_parallel() {
  OUTPUT="$( { bin/phpcbf --no-cache --parallel=2 --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassOneWithoutStyleError.inc tests/EndToEnd/Fixtures/ClassTwoWithoutStyleError.inc; } 2>&1 )"

  assert_successful_code
  assert_contains "No violations were found" "$OUTPUT"
}

function test_phpcbf_returns_error_on_issues() {
  OUTPUT="$( { bin/phpcbf --no-colors --parallel=1 --no-cache --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassWithStyleError.inc; } 2>&1 )"
  assert_successful_code

  assert_contains "F 1 / 1 (100%)" "$OUTPUT"
  assert_contains "A TOTAL OF 1 ERROR WERE FIXED IN 1 FILE" "$OUTPUT"
}

function test_phpcbf_progressbar_shows_fixes_with_parallel_on() {
  OUTPUT="$( { bin/phpcbf --no-colors --parallel=10 --no-cache --suffix=.fixed --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassWithStyleError.inc; } 2>&1 )"
  assert_successful_code

  assert_contains "F 1 / 1 (100%)" "$OUTPUT"
}

function test_phpcbf_bug_1112() {
  # See https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/1112
  if [[ "$(uname)" == "Darwin" ]]; then
    # Perform some magic with `& fg` to prevent the processes from turning into a background job.
    assert_successful_code "$(bash -ic 'bash --init-file <(echo "echo \"Subprocess\"") -c "bin/phpcbf --no-cache --parallel=2 --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassOneWithoutStyleError.inc tests/EndToEnd/Fixtures/ClassTwoWithoutStyleError.inc" & fg')"
  else
    # This is not needed on Linux / GitHub Actions
    assert_successful_code "$(bash -ic 'bash --init-file <(echo "echo \"Subprocess\"") -c "bin/phpcbf --no-cache --parallel=2 --standard=tests/EndToEnd/Fixtures/endtoend.xml.dist tests/EndToEnd/Fixtures/ClassOneWithoutStyleError.inc tests/EndToEnd/Fixtures/ClassTwoWithoutStyleError.inc"')"
  fi
}
